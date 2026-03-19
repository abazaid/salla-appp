<?php

declare(strict_types=1);

namespace App\Services;

final class DescriptionOptimizer
{
    public function optimize(array $product, array $storeSettings = []): string
    {
        $provider = \App\Config::get('AI_PROVIDER', 'mock');

        if ($provider === 'openai') {
            return (new OpenAIClient())->generateProductDescription($product, $storeSettings);
        }

        $tone = $storeSettings['tone'] ?? 'احترافي مقنع';
        $language = $storeSettings['language'] ?? 'ar';
        $name = $product['name'] ?? 'هذا المنتج';
        $price = $product['price']['amount'] ?? $product['price'] ?? null;
        $current = trim(strip_tags((string) ($product['description'] ?? '')));

        if ($language === 'en') {
            $priceText = $price ? "Priced at {$price} SAR." : '';
            return trim(implode("\n\n", [
                "{$name} is presented with a {$tone} tone to help merchants convert more visitors into buyers.",
                "Highlights:\n- Clear value proposition\n- Easy-to-scan benefits\n- Suitable for product pages inside Salla",
                $priceText,
                $current !== '' ? "Original context: {$current}" : '',
            ]));
        }

        $priceText = $price ? "السعر الحالي {$price} ريال، ما يجعله خيارًا مناسبًا لمن يبحث عن توازن بين القيمة والجودة." : '';

        return trim(implode("\n\n", [
            "{$name} منتج مصاغ بأسلوب {$tone} ليساعد المتجر على عرض الفائدة بشكل أوضح ورفع احتمالية الشراء.",
            "أبرز النقاط:\n- وصف أوضح وأسهل للقراءة\n- إبراز القيمة العملية للمنتج\n- مناسب لصفحات المنتجات داخل سلة",
            $priceText,
            $current !== '' ? "السياق الحالي للمنتج: {$current}" : '',
        ]));
    }
}
