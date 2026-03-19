<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use DateTimeImmutable;
use PDO;

final class SaaSRepository
{
    private PDO $pdo;
    private ?bool $aiUsageHasModeColumn = null;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function upsertStore(array $storeData): int
    {
        $existing = $this->findStoreByMerchantId((int) $storeData['merchant_id']);
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        if ($existing) {
            $stmt = $this->pdo->prepare(
                'UPDATE stores SET store_name = :store_name, store_username = :store_username, owner_email = :owner_email, owner_name = :owner_name, access_token = :access_token, refresh_token = :refresh_token, token_scope = :token_scope, token_expires_at = :token_expires_at, updated_at = :updated_at WHERE id = :id'
            );
            $stmt->execute([
                'id' => $existing['id'],
                'store_name' => $storeData['store_name'],
                'store_username' => $storeData['store_username'],
                'owner_email' => $storeData['owner_email'],
                'owner_name' => $storeData['owner_name'],
                'access_token' => $storeData['access_token'],
                'refresh_token' => $storeData['refresh_token'],
                'token_scope' => $storeData['token_scope'],
                'token_expires_at' => $storeData['token_expires_at'],
                'updated_at' => $now,
            ]);

            return (int) $existing['id'];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO stores (merchant_id, store_name, store_username, owner_email, owner_name, access_token, refresh_token, token_scope, token_expires_at, created_at, updated_at) VALUES (:merchant_id, :store_name, :store_username, :owner_email, :owner_name, :access_token, :refresh_token, :token_scope, :token_expires_at, :created_at, :updated_at)'
        );
        $stmt->execute([
            'merchant_id' => $storeData['merchant_id'],
            'store_name' => $storeData['store_name'],
            'store_username' => $storeData['store_username'],
            'owner_email' => $storeData['owner_email'],
            'owner_name' => $storeData['owner_name'],
            'access_token' => $storeData['access_token'],
            'refresh_token' => $storeData['refresh_token'],
            'token_scope' => $storeData['token_scope'],
            'token_expires_at' => $storeData['token_expires_at'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function ensureOwnerUser(int $storeId, string $email, ?string $fullName): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $existing = $stmt->fetch();
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        if ($existing) {
            $update = $this->pdo->prepare('UPDATE users SET store_id = :store_id, full_name = :full_name, updated_at = :updated_at WHERE id = :id');
            $update->execute([
                'id' => $existing['id'],
                'store_id' => $storeId,
                'full_name' => $fullName,
                'updated_at' => $now,
            ]);

            return $this->findUserByEmail($email) ?? $existing;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO users (store_id, email, full_name, role, invited_at, created_at, updated_at) VALUES (:store_id, :email, :full_name, :role, :invited_at, :created_at, :updated_at)'
        );
        $insert->execute([
            'store_id' => $storeId,
            'email' => $email,
            'full_name' => $fullName,
            'role' => 'owner',
            'invited_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->findUserByEmail($email) ?? [];
    }

    public function upsertSubscription(int $storeId, array $subscription): void
    {
        $stmt = $this->pdo->prepare('SELECT id FROM subscriptions WHERE store_id = :store_id LIMIT 1');
        $stmt->execute(['store_id' => $storeId]);
        $existing = $stmt->fetch();
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $payload = [
            'store_id' => $storeId,
            'status' => $subscription['status'] ?? 'trial',
            'plan_name' => $subscription['plan_name'] ?? 'starter',
            'product_quota' => (int) ($subscription['product_quota'] ?? 0),
            'used_products' => (int) ($subscription['used_products'] ?? 0),
            'period_started_at' => $this->toSqlDate($subscription['period_started_at'] ?? null),
            'period_ends_at' => $this->toSqlDate($subscription['period_ends_at'] ?? null),
            'updated_at' => $now,
        ];

        if ($existing) {
            $update = $this->pdo->prepare(
                'UPDATE subscriptions SET status = :status, plan_name = :plan_name, product_quota = :product_quota, used_products = :used_products, period_started_at = :period_started_at, period_ends_at = :period_ends_at, updated_at = :updated_at WHERE store_id = :store_id'
            );
            $update->execute($payload);
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO subscriptions (store_id, status, plan_name, product_quota, used_products, period_started_at, period_ends_at, created_at, updated_at) VALUES (:store_id, :status, :plan_name, :product_quota, :used_products, :period_started_at, :period_ends_at, :created_at, :updated_at)'
        );
        $insert->execute($payload + ['created_at' => $now]);
    }

    public function createPasswordResetToken(int $userId, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, :expires_at, :created_at)');
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function findValidPasswordResetToken(string $plainToken): ?array
    {
        $stmt = $this->pdo->query('SELECT prt.*, u.email, u.full_name, u.store_id FROM password_reset_tokens prt INNER JOIN users u ON u.id = prt.user_id WHERE prt.used_at IS NULL ORDER BY prt.id DESC');
        $records = $stmt->fetchAll();

        foreach ($records as $record) {
            if (password_verify($plainToken, (string) $record['token_hash']) && strtotime((string) $record['expires_at']) > time()) {
                return $record;
            }
        }

        return null;
    }

    public function setUserPassword(int $userId, string $passwordHash): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :password_hash, password_set_at = :password_set_at, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $userId,
            'password_hash' => $passwordHash,
            'password_set_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function markResetTokenUsed(int $tokenId): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_reset_tokens SET used_at = :used_at WHERE id = :id');
        $stmt->execute([
            'id' => $tokenId,
            'used_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT u.*, s.merchant_id, s.store_name FROM users u INNER JOIN stores s ON s.id = u.store_id WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function findStoreById(int $storeId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, sub.status AS subscription_status, sub.plan_name, sub.product_quota, sub.used_products, sub.period_started_at, sub.period_ends_at FROM stores s LEFT JOIN subscriptions sub ON sub.store_id = s.id WHERE s.id = :id LIMIT 1');
        $stmt->execute(['id' => $storeId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function findStoreByMerchantId(int $merchantId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, sub.status AS subscription_status, sub.plan_name, sub.product_quota, sub.used_products, sub.period_started_at, sub.period_ends_at FROM stores s LEFT JOIN subscriptions sub ON sub.store_id = s.id WHERE s.merchant_id = :merchant_id LIMIT 1');
        $stmt->execute(['merchant_id' => $merchantId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function incrementUsageByMerchantId(int $merchantId): void
    {
        $store = $this->findStoreByMerchantId($merchantId);

        if (!$store) {
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE subscriptions SET used_products = used_products + 1, updated_at = :updated_at WHERE store_id = :store_id');
        $stmt->execute([
            'store_id' => $store['id'],
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function syncSubscriptionByMerchantId(int $merchantId, array $subscription): void
    {
        $store = $this->findStoreByMerchantId($merchantId);

        if (!$store) {
            return;
        }

        $this->upsertSubscription((int) $store['id'], $subscription);
    }

    public function listStores(): array
    {
        $stmt = $this->pdo->query(
            'SELECT s.id, s.merchant_id, s.store_name, s.store_username, s.owner_email, s.owner_name, s.created_at, sub.status AS subscription_status, sub.plan_name, sub.product_quota, sub.used_products, sub.period_ends_at
             FROM stores s
             LEFT JOIN subscriptions sub ON sub.store_id = s.id
             ORDER BY s.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function dashboardStats(): array
    {
        $stats = [
            'stores_count' => 0,
            'active_subscriptions' => 0,
            'trial_subscriptions' => 0,
            'total_quota' => 0,
            'total_used' => 0,
        ];

        $stores = $this->listStores();
        $stats['stores_count'] = count($stores);

        foreach ($stores as $store) {
            $status = (string) ($store['subscription_status'] ?? '');
            if ($status === 'active') {
                $stats['active_subscriptions']++;
            }
            if ($status === 'trial') {
                $stats['trial_subscriptions']++;
            }
            $stats['total_quota'] += (int) ($store['product_quota'] ?? 0);
            $stats['total_used'] += (int) ($store['used_products'] ?? 0);
        }

        return $stats;
    }

    public function updateStoreSubscription(int $storeId, array $payload): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE subscriptions SET status = :status, plan_name = :plan_name, product_quota = :product_quota, used_products = :used_products, period_started_at = :period_started_at, period_ends_at = :period_ends_at, updated_at = :updated_at WHERE store_id = :store_id'
        );
        $stmt->execute([
            'store_id' => $storeId,
            'status' => $payload['status'],
            'plan_name' => $payload['plan_name'],
            'product_quota' => $payload['product_quota'],
            'used_products' => $payload['used_products'],
            'period_started_at' => $this->toSqlDate($payload['period_started_at'] ?? null),
            'period_ends_at' => $this->toSqlDate($payload['period_ends_at'] ?? null),
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function deleteStore(int $storeId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM stores WHERE id = :id');
        $stmt->execute(['id' => $storeId]);
    }

    public function logAdminActivity(string $adminEmail, string $action, ?string $targetType = null, ?string $targetId = null, ?array $details = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_activity_logs (admin_email, action, target_type, target_id, details_json, created_at) VALUES (:admin_email, :action, :target_type, :target_id, :details_json, :created_at)'
        );
        $stmt->execute([
            'admin_email' => $adminEmail,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details_json' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function listAdminActivityLogs(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_activity_logs ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function logAiUsage(int $storeId, ?int $productId, string $model, array $usageCost, ?string $mode = null): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $payload = [
            'store_id' => $storeId,
            'product_id' => $productId,
            'model' => $model,
            'input_tokens' => $usageCost['input_tokens'] ?? 0,
            'output_tokens' => $usageCost['output_tokens'] ?? 0,
            'cached_input_tokens' => $usageCost['cached_input_tokens'] ?? 0,
            'total_tokens' => $usageCost['total_tokens'] ?? 0,
            'input_cost_usd' => $usageCost['input_cost_usd'] ?? 0,
            'output_cost_usd' => $usageCost['output_cost_usd'] ?? 0,
            'total_cost_usd' => $usageCost['total_cost_usd'] ?? 0,
            'created_at' => $now,
        ];

        if ($this->hasAiUsageModeColumn()) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO ai_usage_logs (store_id, product_id, mode, model, input_tokens, output_tokens, cached_input_tokens, total_tokens, input_cost_usd, output_cost_usd, total_cost_usd, created_at)
                 VALUES (:store_id, :product_id, :mode, :model, :input_tokens, :output_tokens, :cached_input_tokens, :total_tokens, :input_cost_usd, :output_cost_usd, :total_cost_usd, :created_at)'
            );
            $stmt->execute($payload + ['mode' => $mode ?: 'unknown']);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO ai_usage_logs (store_id, product_id, model, input_tokens, output_tokens, cached_input_tokens, total_tokens, input_cost_usd, output_cost_usd, total_cost_usd, created_at)
             VALUES (:store_id, :product_id, :model, :input_tokens, :output_tokens, :cached_input_tokens, :total_tokens, :input_cost_usd, :output_cost_usd, :total_cost_usd, :created_at)'
        );
        $stmt->execute($payload);
    }

    public function aiUsageSummary(): array
    {
        $row = $this->pdo->query('SELECT COUNT(*) AS runs_count, COALESCE(SUM(total_cost_usd),0) AS total_cost_usd, COALESCE(SUM(input_tokens),0) AS input_tokens, COALESCE(SUM(output_tokens),0) AS output_tokens FROM ai_usage_logs')->fetch();
        return $row ?: [
            'runs_count' => 0,
            'total_cost_usd' => 0,
            'input_tokens' => 0,
            'output_tokens' => 0,
        ];
    }

    public function storeAiUsageSummary(int $storeId): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS runs_count, COALESCE(SUM(total_cost_usd),0) AS total_cost_usd, COALESCE(SUM(input_tokens),0) AS input_tokens, COALESCE(SUM(output_tokens),0) AS output_tokens FROM ai_usage_logs WHERE store_id = :store_id');
        $stmt->execute(['store_id' => $storeId]);
        $row = $stmt->fetch();
        return $row ?: [
            'runs_count' => 0,
            'total_cost_usd' => 0,
            'input_tokens' => 0,
            'output_tokens' => 0,
        ];
    }

    public function aiUsageSummaryByMode(): array
    {
        return $this->aiUsageByMode(null);
    }

    public function storeAiUsageSummaryByMode(int $storeId): array
    {
        return $this->aiUsageByMode($storeId);
    }

    private function aiUsageByMode(?int $storeId): array
    {
        $labels = [
            'description' => 'وصف المنتجات',
            'seo' => 'سيو المنتج',
            'all' => 'وصف + سيو المنتج',
            'image_alt' => 'ALT الصور',
            'image_alt_bulk' => 'ALT الصور (جملة)',
            'store_seo' => 'سيو المتجر',
            'unknown' => 'غير مصنف',
        ];
        $summary = [];

        foreach ($labels as $key => $label) {
            $summary[$key] = [
                'mode' => $key,
                'label' => $label,
                'runs_count' => 0,
                'total_cost_usd' => 0.0,
                'input_tokens' => 0,
                'output_tokens' => 0,
            ];
        }

        if (!$this->hasAiUsageModeColumn()) {
            $overall = $storeId === null ? $this->aiUsageSummary() : $this->storeAiUsageSummary($storeId);
            $summary['unknown'] = [
                'mode' => 'unknown',
                'label' => $labels['unknown'],
                'runs_count' => (int) ($overall['runs_count'] ?? 0),
                'total_cost_usd' => (float) ($overall['total_cost_usd'] ?? 0),
                'input_tokens' => (int) ($overall['input_tokens'] ?? 0),
                'output_tokens' => (int) ($overall['output_tokens'] ?? 0),
            ];
            return array_values(array_filter($summary, static fn (array $row): bool => $row['runs_count'] > 0 || $row['mode'] === 'unknown'));
        }

        $sql = 'SELECT COALESCE(mode, "unknown") AS mode, COUNT(*) AS runs_count, COALESCE(SUM(total_cost_usd),0) AS total_cost_usd, COALESCE(SUM(input_tokens),0) AS input_tokens, COALESCE(SUM(output_tokens),0) AS output_tokens FROM ai_usage_logs';
        $params = [];
        if ($storeId !== null) {
            $sql .= ' WHERE store_id = :store_id';
            $params['store_id'] = $storeId;
        }
        $sql .= ' GROUP BY COALESCE(mode, "unknown")';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $mode = (string) ($row['mode'] ?? 'unknown');
            if (!isset($summary[$mode])) {
                $summary[$mode] = [
                    'mode' => $mode,
                    'label' => $mode,
                    'runs_count' => 0,
                    'total_cost_usd' => 0.0,
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                ];
            }
            $summary[$mode]['runs_count'] = (int) ($row['runs_count'] ?? 0);
            $summary[$mode]['total_cost_usd'] = (float) ($row['total_cost_usd'] ?? 0);
            $summary[$mode]['input_tokens'] = (int) ($row['input_tokens'] ?? 0);
            $summary[$mode]['output_tokens'] = (int) ($row['output_tokens'] ?? 0);
        }

        return array_values(array_filter($summary, static fn (array $row): bool => $row['runs_count'] > 0));
    }

    private function hasAiUsageModeColumn(): bool
    {
        if ($this->aiUsageHasModeColumn !== null) {
            return $this->aiUsageHasModeColumn;
        }

        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM ai_usage_logs LIKE 'mode'");
            $this->aiUsageHasModeColumn = (bool) $stmt->fetch();
        } catch (\Throwable) {
            $this->aiUsageHasModeColumn = false;
        }

        return $this->aiUsageHasModeColumn;
    }

    private function toSqlDate(?string $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        $timestamp = strtotime($date);
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}
