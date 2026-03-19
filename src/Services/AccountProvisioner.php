<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use App\Repositories\SaaSRepository;
use App\Support\Database;

final class AccountProvisioner
{
    public function provisionFromStoreAuthorize(string $merchantId, array $tokenPayload, array $merchantInfo, array $subscription): ?array
    {
        if (!Database::isAvailable()) {
            return null;
        }

        $merchant = $merchantInfo['data']['merchant'] ?? [];
        $ownerEmail = (string) ($merchantInfo['data']['email'] ?? '');
        $ownerName = (string) ($merchantInfo['data']['name'] ?? ($merchant['name'] ?? 'Store Owner'));

        if ($ownerEmail === '') {
            return null;
        }

        $repository = new SaaSRepository();
        $storeId = $repository->upsertStore([
            'merchant_id' => (int) $merchantId,
            'store_name' => $merchant['name'] ?? null,
            'store_username' => $merchant['username'] ?? null,
            'owner_email' => $ownerEmail,
            'owner_name' => $ownerName,
            'access_token' => $tokenPayload['access_token'] ?? null,
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'token_scope' => $tokenPayload['scope'] ?? null,
            'token_expires_at' => isset($tokenPayload['expires']) ? date('Y-m-d H:i:s', (int) $tokenPayload['expires']) : null,
        ]);

        $user = $repository->ensureOwnerUser($storeId, $ownerEmail, $ownerName);
        $repository->upsertSubscription($storeId, $subscription);

        $rawToken = bin2hex(random_bytes(32));
        $repository->createPasswordResetToken(
            (int) $user['id'],
            password_hash($rawToken, PASSWORD_DEFAULT),
            date('Y-m-d H:i:s', strtotime('+24 hours'))
        );

        $appUrl = rtrim((string) Config::get('APP_URL', 'http://localhost:8000'), '/');
        $setPasswordUrl = $appUrl . '/set-password?token=' . urlencode($rawToken);
        $mailSent = (new Mailer())->sendPasswordReset($ownerEmail, $ownerName, $setPasswordUrl);

        return [
            'owner_email' => $ownerEmail,
            'owner_name' => $ownerName,
            'set_password_url' => $setPasswordUrl,
            'mail_sent' => $mailSent,
        ];
    }
}
