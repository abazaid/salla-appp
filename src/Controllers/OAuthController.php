<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\StoreRepository;
use App\Services\SallaApiClient;
use App\Support\Request;
use App\Support\Response;

final class OAuthController
{
    public function callback(): void
    {
        $code = (string) Request::query('code', '');
        $state = (string) Request::query('state', '');

        if ($code === '') {
            Response::json([
                'success' => false,
                'message' => 'Missing authorization code.',
            ], 422);
            return;
        }

        $client = new SallaApiClient();
        $repository = new StoreRepository();

        try {
            $tokenPayload = $client->exchangeCodeForToken($code);
            $accessToken = $tokenPayload['access_token'] ?? null;

            if (!$accessToken) {
                Response::json([
                    'success' => false,
                    'message' => 'Token exchange failed.',
                    'payload' => $tokenPayload,
                ], 400);
                return;
            }

            $merchantInfo = $client->getUserInfo($accessToken);
            $merchantId = (string) (
                $merchantInfo['data']['merchant']['id']
                ?? $merchantInfo['data']['store']['id']
                ?? $merchantInfo['data']['id']
                ?? 'unknown'
            );

            $existingStore = $repository->find($merchantId);
            $isReconnect = $existingStore !== null;

            $repository->save($merchantId, [
                'merchant_id' => $merchantId,
                'token_payload' => $tokenPayload,
                'merchant_info' => $merchantInfo,
                'settings' => $existingStore['settings'] ?? [
                    'tone' => 'احترافي مقنع',
                    'language' => 'ar',
                    'mode' => 'manual_review',
                ],
                'subscription' => $existingStore['subscription'] ?? [
                    'status' => 'trial',
                    'plan_name' => 'starter',
                    'product_quota' => 20,
                    'used_products' => 0,
                    'period_started_at' => date('c'),
                    'period_ends_at' => date('c', strtotime('+30 days')),
                    'last_event' => 'oauth.connected',
                ],
                'usage_logs' => $existingStore['usage_logs'] ?? [],
            ]);

            // Restore session for reconnect
            if ($isReconnect) {
                $store = (new \App\Repositories\SaaSRepository())->findStoreByMerchantId((int) $merchantId);
                if ($store) {
                    $_SESSION['store_id'] = (int) $store['id'];
                }
            }

            header('Location: /dashboard');
            exit;
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
