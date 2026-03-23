<?php

declare(strict_types=1);

namespace App\Support;

final class Plans
{
    public const BUDGET_TRIAL = 'budget_trial';
    public const STARTER = 'starter';
    public const GROWTH = 'growth';
    public const PRO = 'pro';

    public static function all(): array
    {
        return [
            self::BUDGET_TRIAL => [
                'id' => self::BUDGET_TRIAL,
                'name_ar' => 'تجربة اقتصادية',
                'name_en' => 'Budget Trial',
                'name' => 'Budget Trial',
                'color' => 'green',
                'icon' => '🟢',
                'badge' => '🧪',
                'price_sar' => 5,
                'price_usd' => 1.33,
                'description_ar' => 'جرّب قوة RankX SEO بسعر رمزي وشاهد النتائج بنفسك',
                'description_en' => 'Try RankX SEO at a low cost and see real results',
                'is_featured' => false,
                'quotas' => [
                    'product_description' => 25,
                    'product_seo' => 15,
                    'image_alt' => 25,
                    'keyword_research' => 5,
                    'domain_seo' => 1,
                ],
            ],
            self::STARTER => [
                'id' => self::STARTER,
                'name_ar' => 'الخطة الأساسية',
                'name_en' => 'Starter Plan',
                'name' => 'Starter Plan',
                'color' => 'blue',
                'icon' => '🔵',
                'badge' => '🧩',
                'price_sar' => 29,
                'price_usd' => 7.73,
                'description_ar' => 'ابدأ تحسين متجرك ورفع ترتيب منتجاتك',
                'description_en' => 'Start optimizing your store and improve product rankings',
                'is_featured' => false,
                'quotas' => [
                    'product_description' => 80,
                    'product_seo' => 40,
                    'image_alt' => 80,
                    'keyword_research' => 10,
                    'domain_seo' => 3,
                ],
            ],
            self::GROWTH => [
                'id' => self::GROWTH,
                'name_ar' => 'الخطة المتقدمة',
                'name_en' => 'Growth Plan',
                'name' => 'Growth Plan',
                'color' => 'purple',
                'icon' => '🟣',
                'badge' => '🚀',
                'price_sar' => 79,
                'price_usd' => 21.07,
                'description_ar' => 'الخيار الأفضل لنمو المتاجر وزيادة المبيعات',
                'description_en' => 'Best choice for growing stores and increasing sales',
                'is_featured' => true,
                'quotas' => [
                    'product_description' => 260,
                    'product_seo' => 140,
                    'image_alt' => 260,
                    'keyword_research' => 40,
                    'domain_seo' => 12,
                ],
                'extras' => [
                    'activity_logs' => true,
                    'export' => true,
                    'faster_performance' => true,
                ],
            ],
            self::PRO => [
                'id' => self::PRO,
                'name_ar' => 'الخطة الاحترافية',
                'name_en' => 'Pro Plan',
                'name' => 'Pro Plan',
                'color' => 'red',
                'icon' => '🔴',
                'badge' => '👑',
                'price_sar' => 149,
                'price_usd' => 39.73,
                'description_ar' => 'حل متكامل للمتاجر الكبيرة لتحقيق أفضل نتائج SEO',
                'description_en' => 'Advanced solution for large stores aiming for maximum SEO performance',
                'is_featured' => false,
                'quotas' => [
                    'product_description' => 700,
                    'product_seo' => 350,
                    'image_alt' => 700,
                    'keyword_research' => 120,
                    'domain_seo' => 35,
                ],
                'extras' => [
                    'priority_support' => true,
                    'higher_bulk_limits' => true,
                    'activity_logs' => true,
                    'export' => true,
                    'faster_performance' => true,
                ],
            ],
        ];
    }

    public static function get(string $planId): ?array
    {
        return self::all()[$planId] ?? null;
    }

    public static function getTotalQuota(string $planId): int
    {
        $plan = self::get($planId);
        if ($plan === null) {
            return 0;
        }
        return array_sum($plan['quotas']);
    }

    public static function mapFromSalla(?string $sallaPlanName): string
    {
        if ($sallaPlanName === null || $sallaPlanName === '') {
            return self::STARTER;
        }

        $sallaPlanName = strtolower(trim($sallaPlanName));

        $mapping = [
            'budget_trial' => self::BUDGET_TRIAL,
            'budget' => self::BUDGET_TRIAL,
            'trial' => self::BUDGET_TRIAL,
            'starter' => self::STARTER,
            'basic' => self::STARTER,
            'growth' => self::GROWTH,
            'advanced' => self::GROWTH,
            'pro' => self::PRO,
            'professional' => self::PRO,
            'professionals' => self::PRO,
        ];

        foreach ($mapping as $key => $planId) {
            if (str_contains($sallaPlanName, $key)) {
                return $planId;
            }
        }

        return self::STARTER;
    }

    public static function quotaLabel(string $quotaKey): string
    {
        $labels = [
            'product_description' => 'تحسين وصف منتج',
            'product_seo' => 'تحسين SEO منتج',
            'image_alt' => 'تحسين ALT صور',
            'keyword_research' => 'عمليات كلمات مفتاحية',
            'domain_seo' => 'تحليل سيو دومين',
        ];

        return $labels[$quotaKey] ?? $quotaKey;
    }

    public static function quotaLabelEn(string $quotaKey): string
    {
        $labels = [
            'product_description' => 'Product Description Optimizations',
            'product_seo' => 'Product SEO Optimizations',
            'image_alt' => 'Image ALT Optimizations',
            'keyword_research' => 'Keyword Research Requests',
            'domain_seo' => 'Domain SEO Analyses',
        ];

        return $labels[$quotaKey] ?? $quotaKey;
    }
}
