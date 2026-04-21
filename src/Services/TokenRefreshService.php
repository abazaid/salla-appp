<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use App\Repositories\SaaSRepository;
use App\Repositories\StoreRepository;
use App\Support\Database;

final class TokenRefreshService
{
    private const TOKEN_LIFETIME_DAYS = 14;

    public function __construct(
        private readonly StoreRepository $storeRepository = new StoreRepository(),
        private readonly SallaApiClient $sallaClient = new SallaApiClient()
    ) {
    }

    public function refreshDueTokens(bool $force = false): array
    {
        $lockHandle = $this->acquireLock();
        if ($lockHandle === null) {
            return [
                'success' => false,
                'message' => 'Token refresh is already running.',
                'stats' => [
                    'total' => 0,
                    'due' => 0,
                    'refreshed' => 0,
                    'failed' => 0,
                    'skipped' => 0,
                ],
            ];
        }

        try {
            $days = $this->normalizeRefreshDays((int) Config::get('SALLA_TOKEN_REFRESH_DAYS', 10));
            $stores = $this->storeRepository->all();
            $now = time();
            $stats = [
                'total' => count($stores),
                'due' => 0,
                'refreshed' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];
            $errors = [];

            foreach ($stores as $merchantId => $store) {
                if (!is_array($store)) {
                    $stats['skipped']++;
                    continue;
                }

                $payload = (array) ($store['token_payload'] ?? []);
                $refreshToken = trim((string) ($payload['refresh_token'] ?? ''));
                if ($refreshToken === '') {
                    $stats['skipped']++;
                    continue;
                }

                if (!$force && !$this->isRefreshDue($payload, $days, $now)) {
                    $stats['skipped']++;
                    continue;
                }

                $stats['due']++;

                try {
                    $newPayload = $this->sallaClient->refreshAccessToken($refreshToken);
                    $mergedPayload = $this->mergeTokenPayload($payload, $newPayload, $now);

                    $this->storeRepository->save((string) $merchantId, [
                        'token_payload' => $mergedPayload,
                    ]);

                    $this->syncDatabaseTokens((string) $merchantId, $mergedPayload);
                    $stats['refreshed']++;
                } catch (\Throwable $exception) {
                    $stats['failed']++;
                    $errors[] = [
                        'merchant_id' => (string) $merchantId,
                        'message' => $exception->getMessage(),
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Token refresh run completed.',
                'settings' => [
                    'force' => $force,
                    'refresh_days' => $days,
                    'refreshed_at' => date(DATE_ATOM, $now),
                ],
                'stats' => $stats,
                'errors' => $errors,
            ];
        } finally {
            $this->releaseLock($lockHandle);
        }
    }

    private function isRefreshDue(array $payload, int $refreshEveryDays, int $now): bool
    {
        $expiresAt = $this->resolveExpiresAt($payload, $now);
        if ($expiresAt !== null) {
            $leadDays = max(1, self::TOKEN_LIFETIME_DAYS - $refreshEveryDays);
            return $now >= ($expiresAt - ($leadDays * 86400));
        }

        $refreshedAt = $this->resolveRefreshedAt($payload);
        if ($refreshedAt !== null) {
            return ($now - $refreshedAt) >= ($refreshEveryDays * 86400);
        }

        return true;
    }

    private function mergeTokenPayload(array $currentPayload, array $newPayload, int $now): array
    {
        $merged = $currentPayload;

        foreach (['access_token', 'refresh_token', 'token_type', 'scope'] as $key) {
            if (!array_key_exists($key, $newPayload)) {
                continue;
            }

            $value = is_string($newPayload[$key] ?? null) ? trim((string) $newPayload[$key]) : $newPayload[$key];
            if ($value === null || $value === '') {
                continue;
            }

            $merged[$key] = $value;
        }

        $expiresAt = $this->resolveExpiresAt($newPayload, $now);
        if ($expiresAt !== null) {
            $merged['expires'] = $expiresAt;
        }

        if (isset($newPayload['expires_in']) && is_numeric($newPayload['expires_in'])) {
            $merged['expires_in'] = max(0, (int) $newPayload['expires_in']);
        }

        $merged['refreshed_at'] = date(DATE_ATOM, $now);

        return $merged;
    }

    private function resolveExpiresAt(array $payload, int $baseTimestamp): ?int
    {
        if (isset($payload['expires']) && is_numeric($payload['expires'])) {
            $raw = (int) $payload['expires'];
            if ($raw > ($baseTimestamp + 3600)) {
                return $raw;
            }
            if ($raw > 0 && $raw <= 5184000) {
                return $baseTimestamp + $raw;
            }
        }

        if (isset($payload['expires_in']) && is_numeric($payload['expires_in'])) {
            $seconds = (int) $payload['expires_in'];
            if ($seconds > 0) {
                return $baseTimestamp + $seconds;
            }
        }

        return null;
    }

    private function resolveRefreshedAt(array $payload): ?int
    {
        $raw = (string) ($payload['refreshed_at'] ?? '');
        if ($raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);
        return $timestamp === false ? null : $timestamp;
    }

    private function syncDatabaseTokens(string $merchantId, array $payload): void
    {
        if (!Database::isAvailable()) {
            return;
        }

        $numericMerchantId = (int) $merchantId;
        if ($numericMerchantId <= 0) {
            return;
        }

        $expiresAtUnix = $this->resolveExpiresAt($payload, time());
        $expiresAtSql = $expiresAtUnix !== null ? date('Y-m-d H:i:s', $expiresAtUnix) : null;

        (new SaaSRepository())->updateStoreTokensByMerchantId($numericMerchantId, [
            'access_token' => (string) ($payload['access_token'] ?? ''),
            'refresh_token' => (string) ($payload['refresh_token'] ?? ''),
            'token_scope' => (string) ($payload['scope'] ?? ''),
            'token_expires_at' => $expiresAtSql,
        ]);
    }

    private function normalizeRefreshDays(int $days): int
    {
        if ($days < 1) {
            return 10;
        }

        return min(13, $days);
    }

    private function acquireLock()
    {
        $path = $this->lockFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return null;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        return $handle;
    }

    private function releaseLock($lockHandle): void
    {
        if (!is_resource($lockHandle)) {
            return;
        }

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }

    private function lockFilePath(): string
    {
        $storagePath = Config::get('STORAGE_PATH', APP_BASE_PATH . '/storage');

        if (!preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', (string) $storagePath)) {
            $storagePath = APP_BASE_PATH . '/' . ltrim((string) $storagePath, '/\\');
        }

        return rtrim((string) $storagePath, '/\\') . '/token-refresh.lock';
    }
}
