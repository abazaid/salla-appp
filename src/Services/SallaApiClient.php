<?php

declare(strict_types=1);

namespace App\Services;

final class SallaApiClient
{
    private const API_BASE = 'https://api.salla.dev/admin/v2';
    private const ACCOUNT_BASE = 'https://accounts.salla.sa/oauth2';

    public function __construct(
        private readonly HttpClient $httpClient = new HttpClient()
    ) {
    }

    public function exchangeCodeForToken(string $code): array
    {
        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => \App\Config::get('SALLA_CLIENT_ID'),
            'client_secret' => \App\Config::get('SALLA_CLIENT_SECRET'),
            'redirect_uri' => \App\Config::get('SALLA_REDIRECT_URI'),
            'code' => $code,
        ];

        $response = $this->httpClient->post(self::ACCOUNT_BASE . '/token', $payload);
        return $response['body'];
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = $this->httpClient->get(self::ACCOUNT_BASE . '/user/info', $this->headers($accessToken));
        return $response['body'];
    }

    public function listProducts(string $accessToken): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/products', $this->headers($accessToken));
        return $response['body'];
    }

    public function listBrands(string $accessToken): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/brands', $this->headers($accessToken));
        return $response['body'];
    }

    public function listCategories(string $accessToken): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/categories', $this->headers($accessToken));
        return $response['body'];
    }

    public function categoryDetails(string $accessToken, int $categoryId): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/categories/' . $categoryId, $this->headers($accessToken));
        return $response['body'];
    }

    public function updateCategorySeo(string $accessToken, int $categoryId, string $metaTitle, string $metaDescription): array
    {
        $url = self::API_BASE . '/categories/' . $categoryId;
        $payload = ['meta_title' => $metaTitle, 'meta_description' => $metaDescription];
        
        error_log('Salla API updateCategorySeo: PUT ' . $url);
        error_log('Salla API updateCategorySeo payload: ' . json_encode($payload));
        
        $response = $this->httpClient->put(
            $url,
            $payload,
            $this->headers($accessToken)
        );
        
        error_log('Salla API updateCategorySeo response: ' . json_encode($response));
        
        return $response['body'];
    }

    public function brandDetails(string $accessToken, int $brandId): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/brands/' . $brandId, $this->headers($accessToken));
        return $response['body'];
    }

    public function updateBrandSeo(string $accessToken, int $brandId, string $description, ?string $metaTitle = null, ?string $metaDescription = null): array
    {
        $payload = [
            'description' => $description,
        ];

        if ($metaTitle !== null) {
            $payload['meta_title'] = trim($metaTitle);
        }

        if ($metaDescription !== null) {
            $payload['meta_description'] = trim($metaDescription);
        }

        $response = $this->httpClient->put(self::API_BASE . '/brands/' . $brandId, $payload, $this->headers($accessToken));
        return $response['body'];
    }

    public function productDetails(string $accessToken, int $productId): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/products/' . $productId, $this->headers($accessToken));
        return $response['body'];
    }

    public function updateProduct(string $accessToken, int $productId, string $description, ?string $metadataTitle = null, ?string $metadataDescription = null): array
    {
        $productPayload = $this->productDetails($accessToken, $productId);
        $product = $productPayload['data'] ?? [];
        
        return $this->updateProductContent(
            $accessToken,
            $productId,
            $product,
            $description,
            $metadataTitle,
            $metadataDescription
        );
    }

    public function updateProductContent(string $accessToken, int $productId, array $product, string $description, ?string $metadataTitle = null, ?string $metadataDescription = null): array
    {
        $payload = [
            'name' => $product['name'] ?? 'Product',
            'price' => $product['price']['amount'] ?? $product['price'] ?? 0,
            'description' => $description,
            'status' => $product['status'] ?? 'sale',
            'product_type' => $product['type'] ?? 'product',
            'require_shipping' => $product['require_shipping'] ?? true,
            'weight' => $product['weight'] ?? 0,
            'weight_type' => $product['weight_type'] ?? 'kg',
        ];

        if ($metadataTitle !== null) {
            $resolvedMetadataTitle = trim($metadataTitle);
            if ($resolvedMetadataTitle === '') {
                $resolvedMetadataTitle = (string) ($product['metadata']['title'] ?? ($product['name'] ?? 'Product'));
            }
            $payload['metadata_title'] = $resolvedMetadataTitle;
        }

        if ($metadataDescription !== null) {
            $resolvedMetadataDescription = trim($metadataDescription);
            if ($resolvedMetadataDescription === '') {
                $resolvedMetadataDescription = (string) ($product['metadata']['description'] ?? '');
            }
            if (trim($resolvedMetadataDescription) === '') {
                $resolvedMetadataDescription = strip_tags($description);
            }
            $payload['metadata_description'] = $this->limitText(trim((string) $resolvedMetadataDescription), 300);
        }

        if (isset($product['quantity']) && $product['quantity'] !== null) {
            $payload['quantity'] = $product['quantity'];
        }

        if (!empty($product['maximum_quantity_per_order']) && (int) $product['maximum_quantity_per_order'] >= 1) {
            $payload['maximum_quantity_per_order'] = (int) $product['maximum_quantity_per_order'];
        }

        if (!empty($product['sku'])) {
            $payload['sku'] = $product['sku'];
        }

        $response = $this->httpClient->put(self::API_BASE . '/products/' . $productId, $payload, $this->headers($accessToken));
        return $response['body'];
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

    public function updateImageAlt(string $accessToken, int $imageId, string $alt): array
    {
        $candidates = $this->buildAltCandidates($alt);

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            try {
                $response = $this->httpClient->postForm(
                    self::API_BASE . '/products/images/' . $imageId,
                    ['alt' => $candidate],
                    $this->headers($accessToken)
                );

                return $response['body'];
            } catch (\RuntimeException $exception) {
                if ($this->isSallaAltValidationError($exception->getMessage())) {
                    continue;
                }

                throw $exception;
            }
        }

        throw new \RuntimeException('نص ALT طويل أو غير متوافق مع شروط سلة. تم تقصيره تلقائيًا لكن ما زال مرفوضًا.');
    }

    public function normalizeAltForStore(string $value, string $fallback = 'صورة المنتج'): string
    {
        $normalized = $this->normalizeAltForSalla($value);
        if ($normalized !== '') {
            return $normalized;
        }

        return $this->normalizeAltForSalla($fallback);
    }

    public function getSeoSettings(string $accessToken, string $languageCode = 'ar'): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/seo', $this->headers($accessToken, $languageCode));
        return $response['body'];
    }

    public function updateSeoSettings(
        string $accessToken,
        string $title,
        string $description,
        string $keywords = '',
        ?bool $friendlyUrlsStatus = null,
        string $languageCode = 'ar'
    ): array {
        $payload = [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];

        if ($friendlyUrlsStatus !== null) {
            $payload['friendly_urls_status'] = $friendlyUrlsStatus;
        }

        $response = $this->httpClient->put(self::API_BASE . '/seo', $payload, $this->headers($accessToken, $languageCode));
        return $response['body'];
    }

    public function triggerSeoSitemapRefresh(string $accessToken, string $refreshUrl, string $languageCode = 'ar'): array
    {
        $refreshUrl = trim($refreshUrl);
        if ($refreshUrl === '' || !str_contains($refreshUrl, '://')) {
            return [];
        }

        $response = $this->httpClient->get($refreshUrl, $this->headers($accessToken, $languageCode));
        return is_array($response['body'] ?? null) ? (array) $response['body'] : [];
    }

    private function headers(string $accessToken, string $languageCode = 'ar'): array
    {
        $languageCode = in_array(strtolower(trim($languageCode)), ['ar', 'en'], true)
            ? strtolower(trim($languageCode))
            : 'ar';

        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Accept-Language' => $languageCode,
            'Content-Language' => $languageCode,
        ];
    }

    private function sanitizeAltText(string $value): string
    {
        $value = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function normalizeAltForSalla(string $value): string
    {
        $value = $this->sanitizeAltText(trim($value));
        if ($value === '') {
            return '';
        }

        // Salla validation is effectively character-based for ALT (up to 70 chars).
        // Avoid byte-based truncation because it over-shortens Arabic text.
        while ($value !== '') {
            $charsOk = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') <= 70 : strlen($value) <= 70;
            if ($charsOk) {
                break;
            }

            if (function_exists('mb_substr') && function_exists('mb_strlen')) {
                $len = mb_strlen($value, 'UTF-8');
                $value = rtrim(mb_substr($value, 0, max(0, $len - 1), 'UTF-8'));
            } else {
                $value = rtrim(substr($value, 0, max(0, strlen($value) - 1)));
            }
        }

        return trim($value);
    }

    private function buildAltCandidates(string $value): array
    {
        $clean = $this->sanitizeAltText(trim($value));
        if ($clean === '') {
            return [];
        }

        $candidates = [];
        // Keep retries near the accepted ceiling without collapsing Arabic length.
        foreach ([70, 65, 60, 55, 50] as $maxChars) {
            $candidate = $this->limitByChars($clean, $maxChars);
            $candidate = $this->normalizeAltForSalla($candidate);
            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        return array_values(array_unique($candidates));
    }

    private function limitByChars(string $value, int $maxChars): string
    {
        $value = trim($value);
        if ($value === '' || $maxChars <= 0) {
            return '';
        }

        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            if (mb_strlen($value, 'UTF-8') <= $maxChars) {
                return $value;
            }

            return trim(mb_substr($value, 0, $maxChars, 'UTF-8'));
        }

        if (strlen($value) <= $maxChars) {
            return $value;
        }

        return trim(substr($value, 0, $maxChars));
    }

    private function isSallaAltValidationError(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return str_contains($message, 'alert.invalid_fields')
            || str_contains($message, '"alt"')
            || str_contains($message, 'طول النص')
            || str_contains($message, '70');
    }

}
