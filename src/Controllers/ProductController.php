<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SaaSRepository;
use App\Repositories\StoreRepository;
use App\Services\OpenAICostCalculator;
use App\Services\OpenAIClient;
use App\Services\ProductContentOptimizer;
use App\Services\DataForSeoClient;
use App\Services\SallaApiClient;
use App\Services\SitemapService;
use App\Services\SubscriptionManager;
use App\Support\Database;
use App\Support\Request;
use App\Support\Response;

final class ProductController
{
    public function index(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found. Complete OAuth first.',
            ], 404);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        try {
            $products = (new SallaApiClient())->listProducts($accessToken);
            Response::json([
                'success' => true,
                'merchant_id' => $store['merchant_id'] ?? null,
                'products' => $products['data'] ?? [],
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], $this->resolveProviderStatus($exception->getMessage()));
        }
    }

    public function optimize(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);

        if ($store === null || $productId <= 0) {
            Response::json([
                'success' => false,
                'message' => 'Invalid store or product.',
            ], 422);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $settings = $store['settings'] ?? [];
        $input = Request::input();
        $mode = $this->normalizeMode((string) ($input['mode'] ?? 'all'));
        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        try {
            $client = new SallaApiClient();
            $productPayload = $client->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];

            $optimized = (new ProductContentOptimizer())->optimize($product, $settings, $mode);

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore && isset($optimized['_usage'], $optimized['_model'])) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) $optimized['_usage']);
                    (new SaaSRepository())->logAiUsage(
                        (int) $dbStore['id'],
                        $productId,
                        (string) $optimized['_model'],
                        $usageCost,
                        $mode
                    );
                }
            }

            Response::json([
                'success' => true,
                'mode' => $mode,
                'product_id' => $productId,
                'current_description' => trim(strip_tags((string) ($product['description'] ?? ''))),
                'current_metadata_title' => (string) ($product['metadata']['title'] ?? ''),
                'current_metadata_description' => (string) ($product['metadata']['description'] ?? ''),
                'optimized_description' => $optimized['description'] ?? '',
                'optimized_metadata_title' => $optimized['metadata_title'] ?? '',
                'optimized_metadata_description' => $optimized['metadata_description'] ?? '',
                'usage_cost' => isset($optimized['_usage']) ? (new OpenAICostCalculator())->calculate((array) $optimized['_usage']) : null,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], $this->resolveProviderStatus($exception->getMessage()));
        }
    }

    public function saveDescription(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);

        if ($store === null || $productId <= 0) {
            Response::json([
                'success' => false,
                'message' => 'Invalid store or product.',
            ], 422);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $input = Request::input();
        $mode = $this->normalizeMode((string) ($input['mode'] ?? 'all'));
        $finalDescription = trim((string) ($input['description'] ?? ''));
        $finalMetadataTitle = $this->normalizeMetadataTitle((string) ($input['metadata_title'] ?? ''));
        $finalMetadataDescription = $this->normalizeMetadataDescription((string) ($input['metadata_description'] ?? ''));

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        try {
            $client = new SallaApiClient();
            $productPayload = $client->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];

            $descriptionToSave = $finalDescription !== ''
                ? $finalDescription
                : trim(strip_tags((string) ($product['description'] ?? '')));

            if (in_array($mode, ['description', 'all'], true) && $descriptionToSave === '') {
                Response::json([
                    'success' => false,
                    'message' => 'Description is required.',
                ], 422);
                return;
            }

            $metadataTitleToSave = null;
            $metadataDescriptionToSave = null;

            if ($mode === 'description') {
                // Do not send SEO metadata fields at all in description-only mode.
                // This guarantees Salla won't mutate metadata based on description updates.
                $metadataTitleToSave = null;
                $metadataDescriptionToSave = null;
            } elseif (in_array($mode, ['seo', 'all'], true)) {
                $metadataTitleToSave = $finalMetadataTitle !== '' ? $finalMetadataTitle : null;
                $metadataDescriptionToSave = $finalMetadataDescription !== '' ? $finalMetadataDescription : null;
            }

            $updated = $client->updateProductContent(
                $accessToken,
                $productId,
                $product,
                $descriptionToSave,
                $metadataTitleToSave,
                $metadataDescriptionToSave
            );

            $store = $subscriptionManager->recordOptimization(
                $store,
                $productId,
                $product['name'] ?? null,
                $mode,
                'completed'
            );

            Response::json([
                'success' => true,
                'message' => 'Content saved successfully.',
                'mode' => $mode,
                'saved_description' => $descriptionToSave,
                'saved_metadata_title' => $this->normalizeMetadataTitle((string) ($updated['data']['metadata']['title'] ?? ($product['metadata']['title'] ?? ''))),
                'saved_metadata_description' => $this->normalizeMetadataDescription((string) ($updated['data']['metadata']['description'] ?? ($product['metadata']['description'] ?? ''))),
                'salla_response' => $updated,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function subscription(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'subscription' => $subscriptionManager->summary($store),
        ]);
    }

    public function optimizationSettings(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'settings' => $this->normalizeOptimizationSettings((array) ($store['settings'] ?? [])),
        ]);
    }

    public function saveOptimizationSettings(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $currentSettings = (array) ($store['settings'] ?? []);
        $input = Request::input();
        $mergedInput = array_merge($currentSettings, is_array($input) ? $input : []);
        $normalized = $this->normalizeOptimizationSettings($mergedInput);
        $mergedSettings = array_merge($currentSettings, $normalized);

        $existingSitemapUrl = $this->normalizeSitemapUrl((string) ($currentSettings['sitemap_url'] ?? ''));
        $newSitemapUrl = (string) ($normalized['sitemap_url'] ?? '');
        $sitemapWasTouched = is_array($input) && array_key_exists('sitemap_url', $input);
        $sitemapRefreshRequested = is_array($input) && (bool) ($input['refresh_sitemap_url'] ?? false);
        $sitemapUrlChanged = $newSitemapUrl !== $existingSitemapUrl;
        $sitemapInfoMessage = '';

        if ($newSitemapUrl === '') {
            $mergedSettings['sitemap_links_cache'] = [];
            $mergedSettings['sitemap_links_count'] = 0;
            $mergedSettings['sitemap_last_fetched_at'] = '';
            if ($sitemapWasTouched) {
                $sitemapInfoMessage = 'تم حذف ربط السايت ماب.';
            }
        } else {
            $cachedLinks = is_array($currentSettings['sitemap_links_cache'] ?? null)
                ? (array) $currentSettings['sitemap_links_cache']
                : [];
            // Fetch sitemap links only when sitemap URL is explicitly changed,
            // or when the client explicitly asks to refresh sitemap cache.
            // This prevents unrelated settings saves (e.g., store SEO instructions)
            // from failing بسبب مشاكل قراءة السايت ماب.
            $shouldFetch = ($sitemapWasTouched && $sitemapUrlChanged) || $sitemapRefreshRequested;

            if ($shouldFetch) {
                try {
                    $sitemap = (new SitemapService())->fetchAndParse($newSitemapUrl);
                    $mergedSettings['sitemap_links_cache'] = $sitemap['links'];
                    $mergedSettings['sitemap_links_count'] = (int) ($sitemap['links_count'] ?? 0);
                    $mergedSettings['sitemap_last_fetched_at'] = (string) ($sitemap['fetched_at'] ?? date(DATE_ATOM));
                    $sitemapInfoMessage = 'تم جلب روابط السايت ماب بنجاح: ' . ((int) ($sitemap['links_count'] ?? 0)) . ' رابط.';
                } catch (\Throwable $exception) {
                    Response::json([
                        'success' => false,
                        'message' => 'تعذر قراءة السايت ماب: ' . $this->humanizeProviderError($exception->getMessage()),
                    ], 422);
                    return;
                }
            } else {
                $mergedSettings['sitemap_links_cache'] = $cachedLinks;
                $mergedSettings['sitemap_links_count'] = (int) ($currentSettings['sitemap_links_count'] ?? count($cachedLinks));
                $mergedSettings['sitemap_last_fetched_at'] = (string) ($currentSettings['sitemap_last_fetched_at'] ?? '');
            }
        }

        (new StoreRepository())->save((string) ($store['merchant_id'] ?? ''), [
            'settings' => $mergedSettings,
        ]);

        $responseSettings = $this->normalizeOptimizationSettings($mergedSettings);
        Response::json([
            'success' => true,
            'message' => $sitemapInfoMessage !== ''
                ? ('Optimization settings saved. ' . $sitemapInfoMessage)
                : 'Optimization settings saved.',
            'settings' => $responseSettings,
        ]);
    }

    public function saveStoreSeoInstructions(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $input = Request::input();
        $currentSettings = (array) ($store['settings'] ?? []);
        $updatedSettings = $currentSettings;

        $updatedSettings['store_seo_instructions'] = $this->normalizeOptimizationText(
            (string) ($input['store_seo_instructions'] ?? ($currentSettings['store_seo_instructions'] ?? '')),
            5000
        );

        if (is_array($input) && array_key_exists('output_language', $input)) {
            $updatedSettings['output_language'] = $this->normalizeOutputLanguage((string) ($input['output_language'] ?? ''));
        } elseif (!array_key_exists('output_language', $updatedSettings)) {
            $updatedSettings['output_language'] = '';
        }

        (new StoreRepository())->save((string) ($store['merchant_id'] ?? ''), [
            'settings' => $updatedSettings,
        ]);

        Response::json([
            'success' => true,
            'message' => 'تم حفظ تعليمات سيو المتجر.',
            'settings' => $this->normalizeOptimizationSettings($updatedSettings),
        ]);
    }

    public function operations(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $logs = array_reverse($store['usage_logs'] ?? []);
        $status = trim((string) Request::query('status', 'all'));
        $mode = trim((string) Request::query('mode', 'all'));
        $limitParam = trim((string) Request::query('limit', '20'));

        if ($status !== '' && $status !== 'all') {
            $logs = array_values(array_filter($logs, static function (array $log) use ($status): bool {
                return (string) ($log['status'] ?? 'completed') === $status;
            }));
        }

        if ($mode !== '' && $mode !== 'all') {
            $logs = array_values(array_filter($logs, static function (array $log) use ($mode): bool {
                return (string) ($log['mode'] ?? 'all') === $mode;
            }));
        }

        if ($limitParam !== 'all') {
            $limit = max(1, (int) $limitParam);
            $logs = array_slice($logs, 0, $limit);
        }

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'filters' => [
                'status' => $status,
                'mode' => $mode,
                'limit' => $limitParam,
            ],
            'operations' => array_map(static function (array $log): array {
                return [
                    'product_id' => $log['product_id'] ?? null,
                    'product_name' => $log['product_name'] ?? null,
                    'mode' => $log['mode'] ?? 'all',
                    'status' => $log['status'] ?? 'completed',
                    'used_at' => $log['used_at'] ?? null,
                ];
            }, array_slice($logs, 0, 20)),
        ]);
    }

    public function storeSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        $settings = is_array($store['settings'] ?? null) ? (array) $store['settings'] : [];
        $languageCode = $this->normalizeOutputLanguage((string) ($settings['output_language'] ?? ''));
        if ($languageCode === '') {
            $languageCode = 'ar';
        }

        try {
            $seoResponse = (new SallaApiClient())->getSeoSettings($accessToken, $languageCode);
            $seo = $this->extractStoreSeoFields($seoResponse);
            Response::json([
                'success' => true,
                'merchant_id' => $store['merchant_id'] ?? null,
                'store_domain' => $store['store']['domain'] ?? ($store['store']['url'] ?? null),
                'language_code' => $languageCode,
                'seo' => $seo,
                'raw' => $seoResponse,
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function keywordResearch(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $input = Request::input();
        $keyword = trim((string) ($input['keyword'] ?? ''));
        $country = strtolower(trim((string) ($input['country'] ?? 'sa')));
        $device = strtolower(trim((string) ($input['device'] ?? 'desktop')));
        $language = strtolower(trim((string) ($input['language'] ?? 'ar')));

        if ($keyword === '') {
            Response::json([
                'success' => false,
                'message' => 'أدخل الكلمة المفتاحية أولًا.',
            ], 422);
            return;
        }

        if ($country !== 'sa') {
            Response::json([
                'success' => false,
                'message' => 'الدولة المتاحة حاليًا هي السعودية فقط.',
            ], 422);
            return;
        }

        if (!in_array($device, ['desktop', 'mobile'], true)) {
            $device = 'desktop';
        }

        if (!in_array($language, ['ar', 'en'], true)) {
            $language = 'ar';
        }

        try {
            $result = (new DataForSeoClient())->keywordOverview(
                $keyword,
                $device,
                $country,
                $language
            );

            $historyEntry = [
                'searched_at' => date(DATE_ATOM),
                'keyword' => $keyword,
                'country' => $country,
                'language' => $language,
                'device' => $device,
                'result' => $result,
            ];
            $this->appendKeywordHistory($store, $historyEntry);

            Response::json([
                'success' => true,
                'merchant_id' => $store['merchant_id'] ?? null,
                'keyword_data' => $result,
                'history_entry' => $historyEntry,
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], 500);
        }
    }

    public function domainSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $domainSeo = $this->normalizeDomainSeoSettings((array) (($store['settings'] ?? [])['domain_seo'] ?? []));

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'domain_seo' => $domainSeo,
        ]);
    }

    public function keywordHistory(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $limit = max(1, min(50, (int) Request::query('limit', '15')));
        $history = $this->normalizeKeywordHistory((array) (($store['settings'] ?? [])['keyword_history'] ?? []));

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'history' => array_slice(array_reverse($history), 0, $limit),
        ]);
    }

    public function saveDomainSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $input = Request::input();
        $domain = $this->normalizeDomainInput((string) ($input['domain'] ?? ''));
        $country = strtolower(trim((string) ($input['country'] ?? 'sa')));
        $device = strtolower(trim((string) ($input['device'] ?? 'desktop')));

        if ($domain === '') {
            Response::json([
                'success' => false,
                'message' => 'أدخل الدومين أولًا.',
            ], 422);
            return;
        }

        if ($country !== 'sa') {
            Response::json([
                'success' => false,
                'message' => 'الدولة المتاحة حاليًا هي السعودية فقط.',
            ], 422);
            return;
        }

        if (!in_array($device, ['desktop', 'mobile'], true)) {
            $device = 'desktop';
        }

        $settings = (array) ($store['settings'] ?? []);
        $existing = $this->normalizeDomainSeoSettings((array) ($settings['domain_seo'] ?? []));

        $settings['domain_seo'] = array_merge($existing, [
            'domain' => $domain,
            'country' => $country,
            'device' => $device,
            'saved_at' => date(DATE_ATOM),
        ]);

        (new StoreRepository())->save((string) ($store['merchant_id'] ?? ''), [
            'settings' => $settings,
        ]);

        Response::json([
            'success' => true,
            'message' => 'تم حفظ إعدادات سيو الدومين.',
            'domain_seo' => $settings['domain_seo'],
        ]);
    }

    public function refreshDomainSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $input = Request::input();
        $settings = (array) ($store['settings'] ?? []);
        $existing = $this->normalizeDomainSeoSettings((array) ($settings['domain_seo'] ?? []));

        $domain = $this->normalizeDomainInput((string) ($input['domain'] ?? ($existing['domain'] ?? '')));
        $country = strtolower(trim((string) ($input['country'] ?? ($existing['country'] ?? 'sa'))));
        $device = strtolower(trim((string) ($input['device'] ?? ($existing['device'] ?? 'desktop'))));

        if ($domain === '') {
            Response::json([
                'success' => false,
                'message' => 'احفظ الدومين أولًا ثم حدّث البيانات.',
            ], 422);
            return;
        }

        if ($country !== 'sa') {
            Response::json([
                'success' => false,
                'message' => 'الدولة المتاحة حاليًا هي السعودية فقط.',
            ], 422);
            return;
        }

        if (!in_array($device, ['desktop', 'mobile'], true)) {
            $device = 'desktop';
        }

        try {
            $result = (new DataForSeoClient())->domainOverview($domain, $device);

            $refreshCount = (int) ($existing['refresh_count'] ?? 0) + 1;
            $settings['domain_seo'] = array_merge($existing, [
                'domain' => $domain,
                'country' => $country,
                'device' => $device,
                'refresh_count' => $refreshCount,
                'refreshed_at' => date(DATE_ATOM),
                'last_data' => $result,
            ]);
            $historyEntry = [
                'searched_at' => date(DATE_ATOM),
                'domain' => $domain,
                'country' => $country,
                'device' => $device,
                'result' => $result,
            ];
            $settings['domain_seo_history'] = $this->appendHistoryRow(
                (array) ($settings['domain_seo_history'] ?? []),
                $historyEntry
            );

            (new StoreRepository())->save((string) ($store['merchant_id'] ?? ''), [
                'settings' => $settings,
            ]);

            Response::json([
                'success' => true,
                'message' => 'تم تحديث بيانات سيو الدومين.',
                'domain_seo' => $settings['domain_seo'],
                'history_entry' => $historyEntry,
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], 500);
        }
    }

    public function domainSeoHistory(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $limit = max(1, min(50, (int) Request::query('limit', '15')));
        $history = $this->normalizeDomainSeoHistory((array) (($store['settings'] ?? [])['domain_seo_history'] ?? []));

        Response::json([
            'success' => true,
            'merchant_id' => $store['merchant_id'] ?? null,
            'history' => array_slice(array_reverse($history), 0, $limit),
        ]);
    }

    public function optimizeStoreSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $settings = $store['settings'] ?? [];
        $languageCode = $this->normalizeOutputLanguage((string) ($settings['output_language'] ?? ''));
        if ($languageCode === '') {
            $languageCode = 'ar';
        }

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        try {
            $client = new SallaApiClient();
            $seoResponse = $client->getSeoSettings($accessToken, $languageCode);
            $currentSeo = $this->extractStoreSeoFields($seoResponse);
            $productsResponse = $client->listProducts($accessToken);
            $products = is_array($productsResponse['data'] ?? null) ? $productsResponse['data'] : [];
            $productsContext = $this->buildStoreProductsContext($products);

            $generated = (new OpenAIClient())->generateStoreSeo([
                'store_name' => $store['store']['name'] ?? $store['store']['username'] ?? 'Store',
                'merchant_id' => $store['merchant_id'] ?? null,
                'store_url' => $store['store']['domain'] ?? $store['store']['url'] ?? null,
                'products_count' => count($products),
                'products_sample' => $productsContext['sample_products'],
                'product_topics' => $productsContext['topics'],
            ], is_array($currentSeo) ? $currentSeo : [], $settings);

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore && isset($generated['_usage'], $generated['_model'])) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) $generated['_usage']);
                    (new SaaSRepository())->logAiUsage(
                        (int) $dbStore['id'],
                        0,
                        (string) $generated['_model'],
                        $usageCost,
                        'store_seo'
                    );
                }
            }

            Response::json([
                'success' => true,
                'current_title' => (string) ($currentSeo['title'] ?? ''),
                'current_description' => (string) ($currentSeo['description'] ?? ''),
                'current_keywords' => (string) ($currentSeo['keywords'] ?? ''),
                'optimized_title' => $this->normalizeStoreSeoTitle((string) ($generated['title'] ?? '')),
                'optimized_description' => $this->normalizeStoreSeoDescription((string) ($generated['description'] ?? '')),
                'optimized_keywords' => $this->normalizeStoreSeoKeywords((string) ($generated['keywords'] ?? '')),
                'language_code' => $languageCode,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function saveStoreSeo(): void
    {
        $store = $this->resolveStore();

        if ($store === null) {
            Response::json([
                'success' => false,
                'message' => 'No connected store found.',
            ], 404);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $settings = is_array($store['settings'] ?? null) ? (array) $store['settings'] : [];
        $languageCode = $this->normalizeOutputLanguage((string) ($settings['output_language'] ?? ''));
        if ($languageCode === '') {
            $languageCode = 'ar';
        }

        $input = Request::input();
        $title = $this->normalizeStoreSeoTitle((string) ($input['title'] ?? ''));
        $description = $this->normalizeStoreSeoDescription((string) ($input['description'] ?? ''));
        $keywords = $this->normalizeStoreSeoKeywords((string) ($input['keywords'] ?? ''));

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        if ($title === '' || $description === '') {
            Response::json([
                'success' => false,
                'message' => 'Store SEO title and description are required.',
            ], 422);
            return;
        }

        try {
            $client = new SallaApiClient();
            $updateResponse = $client->updateSeoSettings($accessToken, $title, $description, $keywords, null, $languageCode);

            $refreshSitemapUrl = '';
            $updateData = is_array($updateResponse['data'] ?? null) ? (array) $updateResponse['data'] : [];
            foreach (['refersh_sitemap', 'refresh_sitemap', 'refresh_sitemap_url'] as $refreshKey) {
                $candidate = trim((string) ($updateData[$refreshKey] ?? ''));
                if ($candidate !== '') {
                    $refreshSitemapUrl = $candidate;
                    break;
                }
            }

            $refreshTriggered = false;
            if ($refreshSitemapUrl !== '') {
                try {
                    $client->triggerSeoSitemapRefresh($accessToken, $refreshSitemapUrl, $languageCode);
                    $refreshTriggered = true;
                } catch (\Throwable) {
                    $refreshTriggered = false;
                }
            }

            $fetchedResponse = $client->getSeoSettings($accessToken, $languageCode);
            $appliedSeo = $this->extractStoreSeoFields($fetchedResponse);

            $titleConfirmed = $this->normalizeStoreSeoTitle((string) ($appliedSeo['title'] ?? '')) === $title;
            $descriptionConfirmed = $this->normalizeStoreSeoDescription((string) ($appliedSeo['description'] ?? '')) === $description;
            $keywordsConfirmed = $this->storeSeoKeywordsEquivalent((string) ($appliedSeo['keywords'] ?? ''), $keywords);

            if (!$titleConfirmed || !$descriptionConfirmed || !$keywordsConfirmed) {
                Response::json([
                    'success' => false,
                    'message' => 'لم يتم تأكيد حفظ سيو المتجر داخل سلة. راجع الصلاحيات أو المتجر المرتبط ثم أعد المحاولة.',
                    'expected_seo' => [
                        'title' => $title,
                        'description' => $description,
                        'keywords' => $keywords,
                    ],
                    'applied_seo' => $appliedSeo,
                    'store_domain' => $store['store']['domain'] ?? ($store['store']['url'] ?? null),
                    'language_code' => $languageCode,
                    'refresh_sitemap_url' => $refreshSitemapUrl !== '' ? $refreshSitemapUrl : null,
                    'refresh_triggered' => $refreshTriggered,
                    'update_response' => $updateResponse,
                    'fetch_response' => $fetchedResponse,
                    'subscription' => $subscriptionManager->summary($store),
                ], 409);
                return;
            }
            $store = $subscriptionManager->recordOptimization($store, 0, 'SEO المتجر', 'store_seo', 'completed');

            Response::json([
                'success' => true,
                'message' => 'Store SEO saved successfully.',
                'title' => (string) ($appliedSeo['title'] ?? ''),
                'description' => (string) ($appliedSeo['description'] ?? ''),
                'keywords' => (string) ($appliedSeo['keywords'] ?? ''),
                'applied_seo' => $appliedSeo,
                'store_domain' => $store['store']['domain'] ?? ($store['store']['url'] ?? null),
                'language_code' => $languageCode,
                'refresh_sitemap_url' => $refreshSitemapUrl !== '' ? $refreshSitemapUrl : null,
                'refresh_triggered' => $refreshTriggered,
                'salla_response' => $updateResponse,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function optimizeImageAlt(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);
        $imageId = (int) ($params['imageId'] ?? 0);

        if ($store === null || $productId <= 0 || $imageId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid store, product, or image.'], 422);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $settings = $store['settings'] ?? [];

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        try {
            $productPayload = (new SallaApiClient())->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];
            $image = $this->findProductImage($product, $imageId);

            if ($image === null) {
                Response::json(['success' => false, 'message' => 'Image not found on this product.'], 404);
                return;
            }

            $provider = \App\Config::get('AI_PROVIDER', 'mock');
            $generated = $provider === 'openai'
                ? (new OpenAIClient())->generateImageAlt($product, $image, $settings)
                : [
                    'alt' => trim((string) ($product['name'] ?? 'منتج') . ' - ' . (string) ($image['alt'] ?? 'صورة المنتج')),
                    '_usage' => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0, 'input_tokens_details' => ['cached_tokens' => 0]],
                    '_model' => 'mock',
                ];

            $safeAlt = (new SallaApiClient())->normalizeAltForStore(
                (string) ($generated['alt'] ?? ''),
                (string) ($product['name'] ?? 'صورة المنتج')
            );

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) ($generated['_usage'] ?? []));
                    (new SaaSRepository())->logAiUsage((int) $dbStore['id'], $productId, (string) ($generated['_model'] ?? 'mock'), $usageCost, 'image_alt');
                }
            }

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'image_id' => $imageId,
                'image_url' => $image['url'] ?? null,
                'current_alt' => (string) ($image['alt'] ?? ''),
                'optimized_alt' => $safeAlt,
            ]);
        } catch (\Throwable $exception) {
            Response::json(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function saveImageAlt(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);
        $imageId = (int) ($params['imageId'] ?? 0);

        if ($store === null || $productId <= 0 || $imageId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid store, product, or image.'], 422);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $alt = trim((string) (Request::input()['alt'] ?? ''));

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        $sanitizedAlt = (new SallaApiClient())->normalizeAltForStore($alt, 'صورة المنتج');
        if ($sanitizedAlt === '') {
            Response::json(['success' => false, 'message' => 'Alt text is required.'], 422);
            return;
        }

        try {
            $productPayload = (new SallaApiClient())->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];
            $image = $this->findProductImage($product, $imageId);

            if ($image === null) {
                Response::json(['success' => false, 'message' => 'Image not found on this product.'], 404);
                return;
            }

            $response = (new SallaApiClient())->updateImageAlt($accessToken, $imageId, $sanitizedAlt);
            $store = $subscriptionManager->recordOptimization($store, $productId, $product['name'] ?? null, 'image_alt', 'completed');

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'image_id' => $imageId,
                'saved_alt' => $sanitizedAlt,
                'salla_response' => $response,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], $this->resolveProviderStatus($exception->getMessage()));
        }
    }

    public function optimizeProductImagesAlt(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);

        if ($store === null || $productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid store or product.'], 422);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $input = Request::input();
        $settings = $store['settings'] ?? [];

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        try {
            $client = new SallaApiClient();
            $productPayload = $client->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];
            $images = array_values(array_filter($product['images'] ?? [], static fn (array $image): bool => !empty($image['id'])));
            $selectedIds = array_map('intval', (array) ($input['image_ids'] ?? []));

            if ($selectedIds !== []) {
                $images = array_values(array_filter($images, static fn (array $image): bool => in_array((int) ($image['id'] ?? 0), $selectedIds, true)));
            }

            if ($images === []) {
                Response::json(['success' => false, 'message' => 'No images found to optimize.'], 404);
                return;
            }

            $provider = \App\Config::get('AI_PROVIDER', 'mock');
            $results = [];

            foreach ($images as $image) {
                $generated = $provider === 'openai'
                    ? (new OpenAIClient())->generateImageAlt($product, $image, $settings)
                    : [
                        'alt' => trim((string) ($product['name'] ?? 'منتج') . ' - ' . (string) ($image['alt'] ?? 'صورة المنتج')),
                        '_usage' => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0, 'input_tokens_details' => ['cached_tokens' => 0]],
                        '_model' => 'mock',
                    ];

                $safeAlt = $client->normalizeAltForStore(
                    (string) ($generated['alt'] ?? ''),
                    (string) ($product['name'] ?? 'صورة المنتج')
                );

                $results[] = [
                    'image_id' => (int) ($image['id'] ?? 0),
                    'image_url' => $image['url'] ?? null,
                    'current_alt' => (string) ($image['alt'] ?? ''),
                    'optimized_alt' => $safeAlt,
                ];
            }

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'product_name' => $product['name'] ?? null,
                'images' => $results,
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], $this->resolveProviderStatus($exception->getMessage()));
        }
    }

    public function saveProductImagesAlt(array $params): void
    {
        $store = $this->resolveStore();
        $productId = (int) ($params['id'] ?? 0);

        if ($store === null || $productId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid store or product.'], 422);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);

        if (!$subscriptionManager->canOptimize($store)) {
            Response::json([
                'success' => false,
                'message' => 'Optimization quota reached or subscription inactive.',
                'subscription' => $subscriptionManager->summary($store),
            ], 402);
            return;
        }

        $accessToken = $store['token_payload']['access_token'] ?? null;
        $images = (array) (Request::input()['images'] ?? []);

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        if ($images === []) {
            Response::json(['success' => false, 'message' => 'No images payload provided.'], 422);
            return;
        }

        try {
            $client = new SallaApiClient();
            $productPayload = $client->productDetails($accessToken, $productId);
            $product = $productPayload['data'] ?? [];
            $saved = [];
            $errors = [];

            foreach ($images as $image) {
                $imageId = (int) ($image['image_id'] ?? 0);
                $alt = trim((string) ($image['alt'] ?? ''));
                $safeAlt = $client->normalizeAltForStore($alt, (string) ($product['name'] ?? 'صورة المنتج'));

                if ($imageId <= 0 || $safeAlt === '') {
                    continue;
                }

                try {
                    $client->updateImageAlt($accessToken, $imageId, $safeAlt);
                    $saved[] = [
                        'image_id' => $imageId,
                        'alt' => $safeAlt,
                    ];
                } catch (\Throwable $exception) {
                    $errors[] = [
                        'image_id' => $imageId,
                        'message' => $this->humanizeProviderError($exception->getMessage()),
                    ];
                }
            }

            if ($saved !== []) {
                $store = $subscriptionManager->recordOptimization($store, $productId, $product['name'] ?? null, 'image_alt', 'completed');
            }

            Response::json([
                'success' => $saved !== [],
                'message' => $saved !== []
                    ? ($errors === [] ? 'ALT saved successfully.' : 'Saved with partial failures.')
                    : 'Failed to save ALT for selected images.',
                'saved_images' => $saved,
                'errors' => $errors,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $this->humanizeProviderError($exception->getMessage()),
            ], $this->resolveProviderStatus($exception->getMessage()));
        }
    }

    public function bulkOptimizeVisibleImagesAlt(): void
    {
        $store = $this->resolveStore();
        $input = Request::input();
        $productIds = array_map('intval', (array) ($input['product_ids'] ?? []));

        if ($store === null || $productIds === []) {
            Response::json(['success' => false, 'message' => 'No products selected.'], 422);
            return;
        }

        $subscriptionManager = new SubscriptionManager();
        $store = $subscriptionManager->refreshPeriodIfNeeded($store);
        $accessToken = $store['token_payload']['access_token'] ?? null;
        $settings = $store['settings'] ?? [];

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        $client = new SallaApiClient();
        $processed = [];
        $errors = [];
        $dbStoreId = null;

        if (Database::isAvailable()) {
            $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
            $dbStoreId = $dbStore ? (int) $dbStore['id'] : null;
        }

        foreach ($productIds as $productId) {
            if (!$subscriptionManager->canOptimize($store)) {
                $errors[] = ['product_id' => $productId, 'message' => 'Quota reached before finishing all selected products.'];
                break;
            }

            try {
                $productPayload = $client->productDetails($accessToken, $productId);
                $product = $productPayload['data'] ?? [];
                $images = array_values(array_filter($product['images'] ?? [], static fn (array $image): bool => !empty($image['id'])));

                foreach ($images as $image) {
                    $provider = \App\Config::get('AI_PROVIDER', 'mock');
                    $generated = $provider === 'openai'
                        ? (new OpenAIClient())->generateImageAlt($product, $image, $settings)
                        : [
                            'alt' => trim((string) ($product['name'] ?? 'منتج') . ' - ' . (string) ($image['alt'] ?? 'صورة المنتج')),
                        ];

                    $safeAlt = $client->normalizeAltForStore(
                        (string) ($generated['alt'] ?? ''),
                        (string) ($product['name'] ?? 'صورة المنتج')
                    );
                    $client->updateImageAlt($accessToken, (int) $image['id'], $safeAlt);
                    if ($dbStoreId !== null && isset($generated['_usage'], $generated['_model'])) {
                        $usageCost = (new OpenAICostCalculator())->calculate((array) $generated['_usage']);
                        (new SaaSRepository())->logAiUsage(
                            $dbStoreId,
                            $productId,
                            (string) $generated['_model'],
                            $usageCost,
                            'image_alt_bulk'
                        );
                    }
                }

                $store = $subscriptionManager->recordOptimization($store, $productId, $product['name'] ?? null, 'image_alt_bulk', 'completed');
                $processed[] = [
                    'product_id' => $productId,
                    'product_name' => $product['name'] ?? null,
                    'images_count' => count($images),
                ];
            } catch (\Throwable $exception) {
                $errors[] = [
                    'product_id' => $productId,
                    'message' => $this->humanizeProviderError($exception->getMessage()),
                ];
            }
        }

        Response::json([
            'success' => $processed !== [],
            'processed' => $processed,
            'errors' => $errors,
            'subscription' => $subscriptionManager->summary($store),
        ]);
    }

    private function normalizeMode(string $mode): string
    {
        return in_array($mode, ['description', 'seo', 'all'], true) ? $mode : 'all';
    }

    private function normalizeOptimizationSettings(array $settings): array
    {
        $sitemapLinksCache = is_array($settings['sitemap_links_cache'] ?? null) ? (array) $settings['sitemap_links_cache'] : [];

        return [
            'output_language' => $this->normalizeOutputLanguage((string) ($settings['output_language'] ?? '')),
            'global_instructions' => $this->normalizeOptimizationText((string) ($settings['global_instructions'] ?? ''), 5000),
            'product_description_instructions' => $this->normalizeOptimizationText((string) ($settings['product_description_instructions'] ?? ''), 5000),
            'meta_title_instructions' => $this->normalizeOptimizationText((string) ($settings['meta_title_instructions'] ?? ''), 3000),
            'meta_description_instructions' => $this->normalizeOptimizationText((string) ($settings['meta_description_instructions'] ?? ''), 3000),
            'image_alt_instructions' => $this->normalizeOptimizationText((string) ($settings['image_alt_instructions'] ?? ''), 3000),
            'store_seo_instructions' => $this->normalizeOptimizationText((string) ($settings['store_seo_instructions'] ?? ''), 5000),
            'sitemap_url' => $this->normalizeSitemapUrl((string) ($settings['sitemap_url'] ?? '')),
            'sitemap_links_count' => (int) ($settings['sitemap_links_count'] ?? count($sitemapLinksCache)),
            'sitemap_last_fetched_at' => (string) ($settings['sitemap_last_fetched_at'] ?? ''),
        ];
    }

    private function normalizeSitemapUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (!str_contains($value, '://')) {
            $value = 'https://' . $value;
        }

        $parts = parse_url($value);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        $host = strtolower(trim((string) ($parts['host'] ?? '')));
        if ($host === '') {
            return '';
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '') {
            $path = '/sitemap.xml';
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $scheme . '://' . $host . $path . $query;
    }

    private function normalizeSitemapLinksCache(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = trim((string) ($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $title = trim((string) ($item['title'] ?? ''));
            $type = trim((string) ($item['type'] ?? 'page'));
            if (!in_array($type, ['product', 'category', 'page'], true)) {
                $type = 'page';
            }
            $rows[] = [
                'url' => $url,
                'title' => $this->limitText($title, 180),
                'type' => $type,
            ];
        }

        return array_slice($rows, 0, 1500);
    }

    private function normalizeOutputLanguage(string $value): string
    {
        $value = trim(strtolower($value));
        if ($value === '') {
            return '';
        }

        return in_array($value, ['ar', 'en'], true) ? $value : '';
    }

    private function normalizeDomainSeoSettings(array $settings): array
    {
        $device = (string) ($settings['device'] ?? '');
        if (!in_array($device, ['desktop', 'mobile'], true)) {
            $device = 'desktop';
        }

        return [
            'domain' => $this->normalizeDomainInput((string) ($settings['domain'] ?? '')),
            'country' => 'sa',
            'device' => $device,
            'saved_at' => (string) ($settings['saved_at'] ?? ''),
            'refreshed_at' => (string) ($settings['refreshed_at'] ?? ''),
            'refresh_count' => (int) ($settings['refresh_count'] ?? 0),
            'last_data' => is_array($settings['last_data'] ?? null) ? $settings['last_data'] : null,
        ];
    }

    private function normalizeKeywordHistory(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $result = is_array($item['result'] ?? null) ? $item['result'] : null;
            if ($result === null) {
                continue;
            }
            $rows[] = [
                'searched_at' => (string) ($item['searched_at'] ?? ''),
                'keyword' => trim((string) ($item['keyword'] ?? '')),
                'country' => strtolower(trim((string) ($item['country'] ?? 'sa'))),
                'language' => in_array((string) ($item['language'] ?? 'ar'), ['ar', 'en'], true) ? (string) $item['language'] : 'ar',
                'device' => in_array((string) ($item['device'] ?? 'desktop'), ['desktop', 'mobile'], true) ? (string) $item['device'] : 'desktop',
                'result' => $result,
            ];
        }

        return $rows;
    }

    private function normalizeDomainSeoHistory(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $result = is_array($item['result'] ?? null) ? $item['result'] : null;
            if ($result === null) {
                continue;
            }
            $rows[] = [
                'searched_at' => (string) ($item['searched_at'] ?? ''),
                'domain' => $this->normalizeDomainInput((string) ($item['domain'] ?? '')),
                'country' => 'sa',
                'device' => in_array((string) ($item['device'] ?? 'desktop'), ['desktop', 'mobile'], true) ? (string) $item['device'] : 'desktop',
                'result' => $result,
            ];
        }

        return $rows;
    }

    private function appendKeywordHistory(array $store, array $row): void
    {
        $settings = (array) ($store['settings'] ?? []);
        $history = $this->normalizeKeywordHistory((array) ($settings['keyword_history'] ?? []));
        $settings['keyword_history'] = $this->appendHistoryRow($history, $row);

        (new StoreRepository())->save((string) ($store['merchant_id'] ?? ''), [
            'settings' => $settings,
        ]);
    }

    private function appendHistoryRow(array $history, array $row, int $maxItems = 25): array
    {
        $history[] = $row;
        if (count($history) > $maxItems) {
            $history = array_slice($history, -1 * $maxItems);
        }

        return array_values($history);
    }

    private function normalizeDomainInput(string $domain): string
    {
        $value = trim($domain);
        if ($value === '') {
            return '';
        }

        if (!str_contains($value, '://')) {
            $value = 'https://' . $value;
        }

        $host = (string) parse_url($value, PHP_URL_HOST);
        $host = strtolower(trim($host));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $host = trim($host, '.');

        if ($host === '' || !str_contains($host, '.')) {
            return '';
        }

        return $host;
    }

    private function normalizeOptimizationText(string $value, int $maxLength): string
    {
        $value = str_replace("\r\n", "\n", $value);
        $value = str_replace("\r", "\n", $value);
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return $this->limitText($value, $maxLength);
    }

    private function normalizeMetadataTitle(string $value): string
    {
        return trim($value);
    }

    private function normalizeMetadataDescription(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return $this->limitText($value, 300);
    }

    private function limitText(string $value, int $maxLength): string
    {
        if ($value === '' || $maxLength <= 0) {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value, 'UTF-8') <= $maxLength) {
                return $value;
            }

            return rtrim(mb_substr($value, 0, $maxLength, 'UTF-8'));
        }

        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(substr($value, 0, $maxLength));
    }

    private function normalizeStoreSeoTitle(string $value): string
    {
        return $this->normalizeStoreSeoText($value, 70, false);
    }

    private function normalizeStoreSeoDescription(string $value): string
    {
        return $this->normalizeStoreSeoText($value, 300, false);
    }

    private function normalizeStoreSeoKeywords(string $value): string
    {
        $normalized = $this->normalizeStoreSeoText($value, 300, true);
        if ($normalized === '') {
            return '';
        }

        $parts = preg_split('/\s*[,،]\s*/u', $normalized) ?: [];
        $parts = array_values(array_filter(array_map(static function (string $item): string {
            return trim($item);
        }, $parts), static function (string $item): bool {
            return $item !== '';
        }));

        if ($parts === []) {
            return '';
        }

        return implode(', ', array_slice(array_values(array_unique($parts)), 0, 25));
    }

    private function normalizeStoreSeoText(string $value, int $maxLength, bool $allowComma): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? $value;
        $value = str_replace(['-', '_', '@'], ' ', $value);

        $pattern = $allowComma
            ? '/[^\p{L}\p{N}\s,،]/u'
            : '/[^\p{L}\p{N}\s]/u';
        $value = preg_replace($pattern, ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        return $this->limitText($value, $maxLength);
    }

    private function extractStoreSeoFields(array $response): array
    {
        $payload = $response['data'] ?? $response;
        if (is_array($payload) && isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }
        if (!is_array($payload)) {
            $payload = [];
        }

        $title = (string) (
            $payload['title']
            ?? $payload['meta_title']
            ?? $payload['metadata_title']
            ?? $payload['homepage_title']
            ?? ''
        );

        $description = (string) (
            $payload['description']
            ?? $payload['meta_description']
            ?? $payload['metadata_description']
            ?? $payload['homepage_description']
            ?? ''
        );

        $keywordsRaw = $payload['keywords'] ?? '';
        if (is_array($keywordsRaw)) {
            $keywordsRaw = implode(', ', array_map(static function ($item): string {
                return trim((string) $item);
            }, $keywordsRaw));
        }

        return [
            'title' => $this->normalizeStoreSeoTitle($title),
            'description' => $this->normalizeStoreSeoDescription($description),
            'keywords' => $this->normalizeStoreSeoKeywords((string) $keywordsRaw),
            'friendly_urls_status' => (bool) ($payload['friendly_urls_status'] ?? false),
            'url' => (string) ($payload['url'] ?? ''),
        ];
    }

    private function storeSeoKeywordsEquivalent(string $a, string $b): bool
    {
        $normalize = function (string $value): array {
            $value = $this->normalizeStoreSeoKeywords($value);
            if ($value === '') {
                return [];
            }

            $parts = preg_split('/\s*[,،]\s*/u', $value) ?: [];
            $parts = array_values(array_filter(array_map(function (string $item): string {
                $item = trim($item);
                if (function_exists('mb_strtolower')) {
                    return mb_strtolower($item, 'UTF-8');
                }
                return strtolower($item);
            }, $parts), static function (string $item): bool {
                return $item !== '';
            }));
            sort($parts);
            return $parts;
        };

        return $normalize($a) === $normalize($b);
    }

    private function findProductImage(array $product, int $imageId): ?array
    {
        foreach (($product['images'] ?? []) as $image) {
            if ((int) ($image['id'] ?? 0) === $imageId) {
                return $image;
            }
        }

        return null;
    }

    private function buildStoreProductsContext(array $products): array
    {
        $sample = [];
        $tokens = [];

        foreach (array_slice($products, 0, 60) as $product) {
            $name = trim((string) ($product['name'] ?? ''));
            if ($name !== '') {
                $sample[] = $name;
                foreach ($this->tokenizeText($name) as $token) {
                    $length = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
                    if ($length >= 3) {
                        $tokens[] = $token;
                    }
                }
            }

            $desc = trim(strip_tags((string) ($product['description'] ?? '')));
            if ($desc !== '') {
                foreach ($this->tokenizeText($desc) as $token) {
                    $length = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
                    if ($length >= 4) {
                        $tokens[] = $token;
                    }
                }
            }
        }

        $topics = $this->topFrequentTokens($tokens, 12);

        return [
            'sample_products' => array_values(array_unique(array_slice($sample, 0, 20))),
            'topics' => $topics,
        ];
    }

    private function tokenizeText(string $text): array
    {
        $text = trim($text);
        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static function (string $token): bool {
            if ($token === '' || ctype_digit($token)) {
                return false;
            }

            $stopWords = [
                'من', 'الى', 'إلى', 'على', 'في', 'مع', 'عن', 'او', 'أو', 'the', 'and', 'for',
                'هذا', 'هذه', 'ذلك', 'تلك', 'is', 'are', 'new', 'size', 'color', 'colors',
            ];

            return !in_array($token, $stopWords, true);
        }));
    }

    private function topFrequentTokens(array $tokens, int $limit): array
    {
        if ($tokens === []) {
            return [];
        }

        $counts = [];
        foreach ($tokens as $token) {
            $counts[$token] = ($counts[$token] ?? 0) + 1;
        }

        arsort($counts);
        return array_slice(array_keys($counts), 0, max(1, $limit));
    }

    private function humanizeProviderError(string $message): string
    {
        $trimmed = trim($message);
        if ($trimmed === '') {
            return 'حدث خطأ غير متوقع.';
        }

        $parsed = json_decode($trimmed, true);
        if (!is_array($parsed)) {
            if (preg_match('/\{.*\}/s', $trimmed, $matches) === 1) {
                $parsed = json_decode($matches[0], true);
            }
        }

        if (is_array($parsed)) {
            $fields = is_array($parsed['fields'] ?? null) ? $parsed['fields'] : [];
            if ($fields !== []) {
                $first = array_values($fields)[0] ?? null;
                if (is_array($first)) {
                    $first = $first[0] ?? null;
                }
                if (is_string($first) && trim($first) !== '') {
                    return trim($first);
                }
            }

            if (is_string($parsed['message'] ?? null) && trim((string) $parsed['message']) !== '') {
                return trim((string) $parsed['message']);
            }
        }

        return $trimmed;
    }

    private function resolveProviderStatus(string $message): int
    {
        $normalized = $this->humanizeProviderError($message);
        if (
            str_contains($normalized, 'لا يتجاوز طول النص') ||
            str_contains($normalized, 'لايحتوي على حروف خاصة') ||
            str_contains($normalized, 'Alt text is required')
        ) {
            return 422;
        }

        return 500;
    }

    private function resolveStore(): ?array
    {
        $merchantId = Request::query('merchant_id');
        $stores = (new StoreRepository())->all();

        if ($merchantId !== null && isset($stores[(string) $merchantId])) {
            return $stores[(string) $merchantId];
        }

        $sessionStoreId = (int) ($_SESSION['store_id'] ?? 0);

        if ($sessionStoreId > 0 && Database::isAvailable()) {
            $dbStore = (new SaaSRepository())->findStoreById($sessionStoreId);
            $dbMerchantId = (string) ($dbStore['merchant_id'] ?? '');

            if ($dbMerchantId !== '' && isset($stores[$dbMerchantId])) {
                return $stores[$dbMerchantId];
            }
        }

        return array_values($stores)[0] ?? null;
    }
}
