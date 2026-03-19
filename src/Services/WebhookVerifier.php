<?php

declare(strict_types=1);

namespace App\Services;

final class WebhookVerifier
{
    public function isValid(string $payload, ?string $signature): bool
    {
        $secret = \App\Config::get('SALLA_WEBHOOK_SECRET', '');

        if ($secret === '' || $signature === null) {
            return false;
        }

        $calculated = hash_hmac('sha256', $payload, $secret);
        return hash_equals($calculated, $signature);
    }
}
