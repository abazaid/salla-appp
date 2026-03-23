<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\StoreRepository;
use App\Services\AccountProvisioner;
use App\Services\SallaApiClient;
use App\Services\SubscriptionManager;
use App\Services\WebhookVerifier;
use App\Support\Request;
use App\Support\Response;
use App\Support\Plans;

final class WebhookController
{
    public function handle(): void
    {
        $payload = Request::rawBody();
        $signature = Request::header('X-Salla-Signature');
        $verifier = new WebhookVerifier();

        if (!$verifier->isValid($payload, $signature)) {
            Response::json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
            return;
        }

        $decoded = json_decode($payload, true);
        $event = $decoded['event'] ?? 'unknown';
        $merchantId = (string) ($decoded['merchant'] ?? 'unknown');

        $repository = new StoreRepository();
        $store = $repository->find($merchantId) ?? [];
        $logs = $store['webhook_logs'] ?? [];
        $logs[] = [
            'event' => $event,
            'received_at' => date('c'),
            'payload' => $decoded,
        ];

        $updates = [
            'webhook_logs' => array_slice($logs, -25),
        ];

        $payloadData = $decoded['data'] ?? [];
        $subscriptionManager = new SubscriptionManager();

        if ($event === 'app.store.authorize' && !empty($payloadData['access_token'])) {
            $tokenPayload = [
                'access_token' => $payloadData['access_token'] ?? null,
                'refresh_token' => $payloadData['refresh_token'] ?? null,
                'token_type' => $payloadData['token_type'] ?? 'bearer',
                'scope' => $payloadData['scope'] ?? null,
                'expires' => $payloadData['expires'] ?? null,
            ];

            $updates['merchant_id'] = $merchantId;
            $updates['token_payload'] = $tokenPayload;

            try {
                $merchantInfo = (new SallaApiClient())->getUserInfo((string) $payloadData['access_token']);
                $updates['merchant_info'] = $merchantInfo;
            } catch (\Throwable $exception) {
                $updates['merchant_info_error'] = $exception->getMessage();
            }

            if (empty($store['settings'])) {
                $updates['settings'] = [
                    'tone' => 'احترافي مقنع',
                    'language' => 'ar',
                    'mode' => 'manual_review',
                ];
            }

            if (empty($store['subscription'])) {
                $updates['subscription'] = $subscriptionManager->startTrial($store);
            }

            $externalAccount = (new AccountProvisioner())->provisionFromStoreAuthorize(
                $merchantId,
                $tokenPayload,
                $updates['merchant_info'] ?? ($store['merchant_info'] ?? []),
                $updates['subscription'] ?? ($store['subscription'] ?? [])
            );

            if ($externalAccount !== null) {
                $updates['external_account'] = $externalAccount;
            }
        }

        if (in_array($event, ['app.trial.started'], true)) {
            $updates['subscription'] = $subscriptionManager->startTrial($store);
        }

        if (in_array($event, ['app.subscription.started', 'app.subscription.renewed'], true)) {
            $planName = (string) ($payloadData['plan_name'] ?? '');
            $updates['subscription'] = $subscriptionManager->activateSubscription(
                $store,
                $planName,
                $event
            );
        }

        if (in_array($event, ['app.subscription.canceled', 'app.subscription.expired'], true)) {
            $updates['subscription'] = $subscriptionManager->deactivateSubscription($store, $event);
        }

        if ($event === 'app.subscription.upgraded') {
            $planName = (string) ($payloadData['plan_name'] ?? '');
            $updates['subscription'] = $subscriptionManager->activateSubscription(
                $store,
                $planName,
                $event
            );
        }

        $repository->save($merchantId, $updates);

        Response::json([
            'success' => true,
            'event' => $event,
        ]);
    }
}
