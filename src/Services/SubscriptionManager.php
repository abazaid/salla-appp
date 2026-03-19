<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\StoreRepository;
use App\Repositories\SaaSRepository;
use App\Support\Database;

final class SubscriptionManager
{
    public function refreshPeriodIfNeeded(array $store): array
    {
        $subscription = $store['subscription'] ?? [];
        $merchantId = (string) ($store['merchant_id'] ?? '');

        if ($merchantId === '' || empty($subscription['period_ends_at'])) {
            return $store;
        }

        $endsAt = strtotime((string) $subscription['period_ends_at']);
        $status = (string) ($subscription['status'] ?? 'inactive');

        if ($endsAt !== false && $endsAt < time() && in_array($status, ['active', 'trial'], true)) {
            $subscription['used_products'] = 0;
            $subscription['period_started_at'] = date('c');
            $subscription['period_ends_at'] = date('c', strtotime('+30 days'));
            $subscription['last_event'] = 'period.rolled';

            $store['subscription'] = $subscription;
            (new StoreRepository())->save($merchantId, ['subscription' => $subscription]);
        }

        return $store;
    }

    public function canOptimize(array $store): bool
    {
        $subscription = $store['subscription'] ?? [];
        $status = (string) ($subscription['status'] ?? 'inactive');
        $quota = (int) ($subscription['product_quota'] ?? 0);
        $used = (int) ($subscription['used_products'] ?? 0);

        return in_array($status, ['active', 'trial'], true) && $used < $quota;
    }

    public function recordOptimization(array $store, int $productId, ?string $productName, string $mode = 'all', string $status = 'completed'): array
    {
        $merchantId = (string) ($store['merchant_id'] ?? '');
        $subscription = $store['subscription'] ?? [];
        $subscription['used_products'] = (int) ($subscription['used_products'] ?? 0) + 1;
        $store['subscription'] = $subscription;

        $logs = $store['usage_logs'] ?? [];
        $logs[] = [
            'product_id' => $productId,
            'product_name' => $productName,
            'mode' => $mode,
            'status' => $status,
            'used_at' => date('c'),
        ];
        $store['usage_logs'] = array_slice($logs, -200);

        if ($merchantId !== '') {
            (new StoreRepository())->save($merchantId, [
                'subscription' => $subscription,
                'usage_logs' => $store['usage_logs'],
            ]);

            if (Database::isAvailable()) {
                (new SaaSRepository())->incrementUsageByMerchantId((int) $merchantId);
            }
        }

        return $store;
    }

    public function activateSubscription(array $store, string $planName, int $quota, string $event): array
    {
        $subscription = $store['subscription'] ?? [];
        $subscription['status'] = 'active';
        $subscription['plan_name'] = $planName;
        $subscription['product_quota'] = max($quota, 1);
        $subscription['used_products'] = 0;
        $subscription['period_started_at'] = date('c');
        $subscription['period_ends_at'] = date('c', strtotime('+30 days'));
        $subscription['last_event'] = $event;

        if (!empty($store['merchant_id']) && Database::isAvailable()) {
            (new SaaSRepository())->syncSubscriptionByMerchantId((int) $store['merchant_id'], $subscription);
        }

        return $subscription;
    }

    public function deactivateSubscription(array $store, string $event): array
    {
        $subscription = $store['subscription'] ?? [];
        $subscription['status'] = 'inactive';
        $subscription['last_event'] = $event;

        if (!empty($store['merchant_id']) && Database::isAvailable()) {
            (new SaaSRepository())->syncSubscriptionByMerchantId((int) $store['merchant_id'], $subscription);
        }

        return $subscription;
    }

    public function summary(array $store): array
    {
        $subscription = $store['subscription'] ?? [];
        $quota = (int) ($subscription['product_quota'] ?? 0);
        $used = (int) ($subscription['used_products'] ?? 0);

        return [
            'status' => $subscription['status'] ?? 'inactive',
            'plan_name' => $subscription['plan_name'] ?? null,
            'product_quota' => $quota,
            'used_products' => $used,
            'remaining_products' => max($quota - $used, 0),
            'period_started_at' => $subscription['period_started_at'] ?? null,
            'period_ends_at' => $subscription['period_ends_at'] ?? null,
            'last_event' => $subscription['last_event'] ?? null,
        ];
    }
}
