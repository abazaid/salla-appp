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

    public function productDetails(string $accessToken, int $productId): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/products/' . $productId, $this->headers($accessToken));
        return $response['body'];
    }

    public function updateProductContent(string $accessToken, int $productId, array $product, string $description, ?string $metadataTitle = null, ?string $metadataDescription = null): array
    {
        $resolvedMetadataTitle = $metadataTitle;
        if ($resolvedMetadataTitle === null || trim($resolvedMetadataTitle) === '') {
            $resolvedMetadataTitle = (string) ($product['metadata']['title'] ?? ($product['name'] ?? 'Product'));
        }
        $resolvedMetadataTitle = trim($resolvedMetadataTitle);

        $resolvedMetadataDescription = $metadataDescription;
        if ($resolvedMetadataDescription === null || trim($resolvedMetadataDescription) === '') {
            $resolvedMetadataDescription = (string) ($product['metadata']['description'] ?? '');
        }
        if (trim($resolvedMetadataDescription) === '') {
            $resolvedMetadataDescription = strip_tags($description);
        }
        $resolvedMetadataDescription = $this->limitText(trim((string) $resolvedMetadataDescription), 300);

        $payload = [
            'name' => $product['name'] ?? 'Product',
            'price' => $product['price']['amount'] ?? $product['price'] ?? 0,
            'description' => $description,
            'status' => $product['status'] ?? 'sale',
            'product_type' => $product['type'] ?? 'product',
            'require_shipping' => $product['require_shipping'] ?? true,
            'weight' => $product['weight'] ?? 0,
            'weight_type' => $product['weight_type'] ?? 'kg',
            'metadata_title' => $resolvedMetadataTitle,
            'metadata_description' => $resolvedMetadataDescription,
        ];

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
        $response = $this->httpClient->postForm(
            self::API_BASE . '/products/images/' . $imageId,
            ['alt' => $alt],
            $this->headers($accessToken)
        );

        return $response['body'];
    }

    public function getSeoSettings(string $accessToken): array
    {
        $response = $this->httpClient->get(self::API_BASE . '/seo', $this->headers($accessToken));
        return $response['body'];
    }

    public function updateSeoSettings(
        string $accessToken,
        string $title,
        string $description,
        string $keywords = '',
        ?bool $friendlyUrlsStatus = null
    ): array {
        $payload = [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];

        if ($friendlyUrlsStatus !== null) {
            $payload['friendly_urls_status'] = $friendlyUrlsStatus;
        }

        $response = $this->httpClient->put(self::API_BASE . '/seo', $payload, $this->headers($accessToken));
        return $response['body'];
    }

    private function headers(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];
    }
}
