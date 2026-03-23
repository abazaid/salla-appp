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
            'webhook_logs' => array_slice($logs, -50),
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
                    'output_language' => 'ar',
                    'global_instructions' => "اكتب محتوى عربي احترافي موجه للعميل السعودي.\nركّز على مساعدة العميل في اتخاذ قرار الشراء.\nاجعل النص:\n- واضح\n- سهل القراءة\n- عملي (يفيد العميل فعليًا)\n\nالقواعد:\n- لا تنسخ من المنافسين\n- لا تخترع معلومات أو مواصفات\n- استخدم اسم المنتج + البراند بشكل طبيعي\n- ركّز على الفوائد (مو الوصف فقط)\n- تجنب الحشو والكلمات الفارغة\n- لا تذكر مواقع أو منافسين\n- لا تضع روابط خارجية (فقط روابط داخلية)\n\nالهدف:\n- رفع التحويل (Conversion)\n- تحسين SEO",
                    'product_description_instructions' => "🧩 أهم نقطة: حدد نوع المنتج أولاً!\nأمثلة: ملابس (رجالي/نسائي) | أحذية | إكسسوارات | إلكترونيات | أدوات منزلية | تجميلي\n\n🧠 قواعد حسب نوع المنتج:\n• ملابس: ركّز على الخامة، المقاس، الراحة، الاستخدام\n• إلكترونيات: ركّز على الأداء، المواصفات، الاستخدام العملي\n• تجميلي: ركّز على النتائج، المكونات، الأمان\n\n🧾 وصف المنتج:\n- محتوى مقنع + SEO يساعد العميل يشتري\n- الطول: 800-1200 كلمة (بدون حشو)\n\n🔗 الربط الداخلي:\n- استخدم 2-3 روابط فقط من نفس المتجر\n\n🧱 هيكل الوصف:\n1. مقدمة: تعريف + اسم + البراند + أهم ميزة\n2. H2: نظرة عامة على المنتج\n3. H2: أهم المميزات (Bullet فقط)\n4. H2: المواصفات\n5. H2: التصميم وجودة التصنيع\n6. H2: الأداء وتجربة الاستخدام\n7. H2: تقييمنا للمنتج\n8. H2: طريقة الاستخدام\n9. H2: لماذا يختار العملاء هذا المنتج\n10. H2: لمن يناسب هذا المنتج\n11. H2: لماذا تشتري من متجرنا\n12. H2: منتجات قد تهمك (روابط داخلية)\n13. H2: الأسئلة الشائعة (5-7 أسئلة)\n\n⚠️ تجنب:\n❌ وصف عام يصلح لأي منتج\n❌ اختراع مواصفات\n❌ تكرار الكلمات المفتاحية\n❌ حشو بدون فائدة\n❌ نسخ من المنافسين",
                    'meta_title_instructions' => "🏷️ Meta Title\n- 50-60 حرف\n- يبدأ باسم المنتج\n- الصيغة: اسم المنتج + الفئة + ميزة قوية\n\nمثال: فستان سهرة ساتان نسائي تصميم أنيق وقصة مريحة\n\nتجنب: التكرار، الحشو",
                    'meta_description_instructions' => "📝 Meta Description\n- 140-155 حرف\n- يحتوي اسم المنتج\n- يحفّز على الشراء\n- الصيغة: اشتري + المنتج + ميزة + فائدة + عنصر ثقة\n\nمثال: اشتري فستان سهرة ساتان نسائي بتصميم أنيق وخامة ناعمة مريحة. مثالي للمناسبات ويوفر لك إطلالة راقية بجودة عالية.",
                    'image_alt_instructions' => "🖼️ ALT للصور\n- دقيق: يصف الصورة بشكل صحيح\n- طبيعي: يبدو كجملة عادية\n- واضح: يفهم منه محتوى الصورة\n- يتضمن اسم المنتج عند الإمكان\n- 70-125 حرف\n\nمثال: صورة فستان سهرة نسائي ساتان أرجواني تصميم أنيق",
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

        $appEvents = [
            'app.trial.started' => 'trial',
            'app.trial.ended' => 'trial_ended',
            'app.subscription.started' => 'subscription_started',
            'app.subscription.renewed' => 'subscription_renewed',
            'app.subscription.expired' => 'subscription_expired',
            'app.subscription.upgraded' => 'subscription_upgraded',
        ];

        if (isset($appEvents[$event])) {
            if ($event === 'app.trial.started') {
                $updates['subscription'] = $subscriptionManager->startTrial($store);
            } elseif ($event === 'app.trial.ended') {
                $updates['subscription'] = $subscriptionManager->deactivateSubscription($store, $event);
            } elseif (in_array($event, ['app.subscription.started', 'app.subscription.renewed', 'app.subscription.upgraded'], true)) {
                $planSlug = (string) ($payloadData['slug'] ?? '');
                $planName = (string) ($payloadData['plan_name'] ?? $planSlug);
                $intervalCount = (int) ($payloadData['interval_count'] ?? 30);
                $validTill = (string) ($payloadData['valid_till'] ?? '');

                $updates['subscription'] = $subscriptionManager->activateSubscription(
                    $store,
                    $planName,
                    $event,
                    $intervalCount,
                    $validTill
                );
            } elseif ($event === 'app.subscription.expired') {
                $updates['subscription'] = $subscriptionManager->deactivateSubscription($store, $event);
            }
        }

        $subscriptionEvents = [
            'subscription.created' => 'subscription_created',
            'subscription.charge.succeeded' => 'subscription_charged',
            'subscription.charge.failed' => 'subscription_charge_failed',
            'subscription.cancelled' => 'subscription_cancelled',
            'subscription.updated' => 'subscription_updated',
        ];

        if (isset($subscriptionEvents[$event])) {
            $planSlug = (string) ($payloadData['slug'] ?? '');
            $validTill = (string) ($payloadData['valid_till'] ?? '');
            $intervalCount = (int) ($payloadData['interval_count'] ?? 30);

            if ($event === 'subscription.created' || $event === 'subscription.charge.succeeded') {
                $updates['subscription'] = $subscriptionManager->activateSubscription(
                    $store,
                    $planSlug,
                    $event,
                    $intervalCount,
                    $validTill
                );
            } elseif ($event === 'subscription.cancelled') {
                $updates['subscription'] = $subscriptionManager->deactivateSubscription($store, $event);
            } elseif ($event === 'subscription.updated') {
                $updates['subscription'] = $subscriptionManager->activateSubscription(
                    $store,
                    $planSlug,
                    $event,
                    $intervalCount,
                    $validTill
                );
            }
        }

        $repository->save($merchantId, $updates);

        Response::json([
            'success' => true,
            'event' => $event,
        ]);
    }
}
