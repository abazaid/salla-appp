<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SaaSRepository;
use App\Repositories\StoreRepository;
use App\Services\OpenAICostCalculator;
use App\Services\OpenAIClient;
use App\Services\ProductContentOptimizer;
use App\Services\SallaApiClient;
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
                'message' => $exception->getMessage(),
            ], 500);
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

            $settings = array_merge($settings, array_filter([
                'tone' => $input['tone'] ?? null,
                'language' => $input['language'] ?? null,
            ]));

            $optimized = (new ProductContentOptimizer())->optimize($product, $settings, $mode);

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore && isset($optimized['_usage'], $optimized['_model'])) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) $optimized['_usage']);
                    (new SaaSRepository())->logAiUsage(
                        (int) $dbStore['id'],
                        $productId,
                        (string) $optimized['_model'],
                        $usageCost
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
                'message' => $exception->getMessage(),
            ], 500);
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
        $finalMetadataTitle = trim((string) ($input['metadata_title'] ?? ''));
        $finalMetadataDescription = trim((string) ($input['metadata_description'] ?? ''));

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

            $updated = $client->updateProductContent(
                $accessToken,
                $productId,
                $product,
                $descriptionToSave,
                $finalMetadataTitle !== '' ? $finalMetadataTitle : null,
                $finalMetadataDescription !== '' ? $finalMetadataDescription : null
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
                'saved_metadata_title' => $finalMetadataTitle !== '' ? $finalMetadataTitle : (string) ($product['metadata']['title'] ?? ''),
                'saved_metadata_description' => $finalMetadataDescription !== '' ? $finalMetadataDescription : (string) ($product['metadata']['description'] ?? ''),
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

        try {
            $seo = (new SallaApiClient())->getSeoSettings($accessToken);
            Response::json([
                'success' => true,
                'merchant_id' => $store['merchant_id'] ?? null,
                'seo' => $seo['data'] ?? $seo,
            ]);
        } catch (\Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
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
        $input = Request::input();
        $settings = array_merge($store['settings'] ?? [], array_filter([
            'tone' => $input['tone'] ?? null,
            'language' => $input['language'] ?? null,
        ]));

        if (!$accessToken) {
            Response::json([
                'success' => false,
                'message' => 'Missing access token.',
            ], 400);
            return;
        }

        try {
            $client = new SallaApiClient();
            $seoResponse = $client->getSeoSettings($accessToken);
            $currentSeo = $seoResponse['data'] ?? $seoResponse;

            $generated = (new OpenAIClient())->generateStoreSeo([
                'store_name' => $store['store']['name'] ?? $store['store']['username'] ?? 'Store',
                'merchant_id' => $store['merchant_id'] ?? null,
                'store_url' => $store['store']['domain'] ?? $store['store']['url'] ?? null,
            ], is_array($currentSeo) ? $currentSeo : [], $settings);

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore && isset($generated['_usage'], $generated['_model'])) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) $generated['_usage']);
                    (new SaaSRepository())->logAiUsage(
                        (int) $dbStore['id'],
                        0,
                        (string) $generated['_model'],
                        $usageCost
                    );
                }
            }

            Response::json([
                'success' => true,
                'current_title' => (string) ($currentSeo['title'] ?? ''),
                'current_description' => (string) ($currentSeo['description'] ?? ''),
                'current_keywords' => (string) ($currentSeo['keywords'] ?? ''),
                'optimized_title' => $generated['title'] ?? '',
                'optimized_description' => $generated['description'] ?? '',
                'optimized_keywords' => $generated['keywords'] ?? '',
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
        $input = Request::input();
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $keywords = trim((string) ($input['keywords'] ?? ''));

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
            $response = (new SallaApiClient())->updateSeoSettings($accessToken, $title, $description, $keywords);
            $store = $subscriptionManager->recordOptimization($store, 0, 'SEO المتجر', 'store_seo', 'completed');

            Response::json([
                'success' => true,
                'message' => 'Store SEO saved successfully.',
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords,
                'salla_response' => $response,
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
        $input = Request::input();
        $settings = array_merge($store['settings'] ?? [], array_filter([
            'language' => $input['language'] ?? null,
        ]));

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

            if (Database::isAvailable()) {
                $dbStore = (new SaaSRepository())->findStoreByMerchantId((int) ($store['merchant_id'] ?? 0));
                if ($dbStore) {
                    $usageCost = (new OpenAICostCalculator())->calculate((array) ($generated['_usage'] ?? []));
                    (new SaaSRepository())->logAiUsage((int) $dbStore['id'], $productId, (string) ($generated['_model'] ?? 'mock'), $usageCost);
                }
            }

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'image_id' => $imageId,
                'image_url' => $image['url'] ?? null,
                'current_alt' => (string) ($image['alt'] ?? ''),
                'optimized_alt' => (string) ($generated['alt'] ?? ''),
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

        if ($alt === '') {
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

            $response = (new SallaApiClient())->updateImageAlt($accessToken, $imageId, $alt);
            $store = $subscriptionManager->recordOptimization($store, $productId, $product['name'] ?? null, 'image_alt', 'completed');

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'image_id' => $imageId,
                'saved_alt' => $alt,
                'salla_response' => $response,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json(['success' => false, 'message' => $exception->getMessage()], 500);
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
        $settings = array_merge($store['settings'] ?? [], array_filter([
            'language' => $input['language'] ?? null,
        ]));

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

                $results[] = [
                    'image_id' => (int) ($image['id'] ?? 0),
                    'image_url' => $image['url'] ?? null,
                    'current_alt' => (string) ($image['alt'] ?? ''),
                    'optimized_alt' => (string) ($generated['alt'] ?? ''),
                ];
            }

            Response::json([
                'success' => true,
                'product_id' => $productId,
                'product_name' => $product['name'] ?? null,
                'images' => $results,
            ]);
        } catch (\Throwable $exception) {
            Response::json(['success' => false, 'message' => $exception->getMessage()], 500);
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

            foreach ($images as $image) {
                $imageId = (int) ($image['image_id'] ?? 0);
                $alt = trim((string) ($image['alt'] ?? ''));

                if ($imageId <= 0 || $alt === '') {
                    continue;
                }

                $client->updateImageAlt($accessToken, $imageId, $alt);
                $saved[] = [
                    'image_id' => $imageId,
                    'alt' => $alt,
                ];
            }

            $store = $subscriptionManager->recordOptimization($store, $productId, $product['name'] ?? null, 'image_alt', 'completed');

            Response::json([
                'success' => true,
                'saved_images' => $saved,
                'subscription' => $subscriptionManager->summary($store),
            ]);
        } catch (\Throwable $exception) {
            Response::json(['success' => false, 'message' => $exception->getMessage()], 500);
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
        $settings = array_merge($store['settings'] ?? [], array_filter([
            'language' => $input['language'] ?? null,
        ]));

        if (!$accessToken) {
            Response::json(['success' => false, 'message' => 'Missing access token.'], 400);
            return;
        }

        $client = new SallaApiClient();
        $processed = [];
        $errors = [];

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

                    $client->updateImageAlt($accessToken, (int) $image['id'], (string) $generated['alt']);
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
                    'message' => $exception->getMessage(),
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

    private function findProductImage(array $product, int $imageId): ?array
    {
        foreach (($product['images'] ?? []) as $image) {
            if ((int) ($image['id'] ?? 0) === $imageId) {
                return $image;
            }
        }

        return null;
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
