<?php

declare(strict_types=1);

namespace App\Services;

final class ProductContentOptimizer
{
    public function optimize(array $product, array $storeSettings = [], string $mode = 'all'): array
    {
        $provider = \App\Config::get('AI_PROVIDER', 'mock');
        $mode = in_array($mode, ['description', 'seo', 'all'], true) ? $mode : 'all';

        if ($provider === 'openai') {
            return (new OpenAIClient())->generateProductContent($product, $storeSettings, $mode);
        }

        $description = (new DescriptionOptimizer())->optimize($product, $storeSettings);
        $name = (string) ($product['name'] ?? 'منتج');
        $storeName = (string) ($product['source'] ?? 'المتجر');
        $currentDescription = trim(strip_tags((string) ($product['description'] ?? '')));
        $currentMetadataTitle = trim((string) ($product['metadata']['title'] ?? ''));
        $currentMetadataDescription = trim((string) ($product['metadata']['description'] ?? ''));

        $generatedDescription = $mode === 'seo' ? $currentDescription : $description;
        $generatedMetadataTitle = $mode === 'description' ? $currentMetadataTitle : ($name . ' - ' . $storeName);
        $generatedMetadataDescription = $mode === 'description'
            ? $currentMetadataDescription
            : mb_substr(trim(strip_tags($description)), 0, 160);

        return [
            'description' => $generatedDescription,
            'metadata_title' => $generatedMetadataTitle,
            'metadata_description' => $generatedMetadataDescription,
            '_usage' => [
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0,
                'input_tokens_details' => ['cached_tokens' => 0],
            ],
            '_model' => 'mock',
        ];
    }
}
