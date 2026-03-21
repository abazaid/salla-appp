<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use RuntimeException;

final class OpenAIClient
{
    private const API_BASE = 'https://api.openai.com/v1';

    public function __construct(
        private readonly HttpClient $httpClient = new HttpClient()
    ) {
    }

    public function generateProductDescription(array $product, array $settings = []): string
    {
        $apiKey = Config::get('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $model = Config::get('OPENAI_MODEL', 'gpt-5-mini');
        $reasoningEffort = Config::get('OPENAI_REASONING_EFFORT', 'low');

        $response = $this->httpClient->post(self::API_BASE . '/responses', [
            'model' => $model,
            'reasoning' => [
                'effort' => $reasoningEffort,
            ],
            'input' => $this->buildPrompt($product, $settings),
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ]);

        $body = $response['body'];
        $text = $this->extractText($body);

        if ($text === '') {
            throw new RuntimeException('OpenAI returned an empty description.');
        }

        return trim($text);
    }

    public function generateProductContent(array $product, array $settings = [], string $mode = 'all'): array
    {
        $apiKey = Config::get('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $model = Config::get('OPENAI_MODEL', 'gpt-5-mini');
        $reasoningEffort = Config::get('OPENAI_REASONING_EFFORT', 'low');
        $mode = in_array($mode, ['description', 'seo', 'all'], true) ? $mode : 'all';

        $response = $this->httpClient->post(self::API_BASE . '/responses', [
            'model' => $model,
            'reasoning' => [
                'effort' => $reasoningEffort,
            ],
            'input' => $this->buildContentPrompt($product, $settings, $mode),
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ]);

        $body = $response['body'];
        $text = $this->extractText($body);

        if ($text === '') {
            throw new RuntimeException('OpenAI returned empty content.');
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('OpenAI returned invalid JSON content.');
        }

        $currentDescription = trim(strip_tags((string) ($product['description'] ?? '')));
        $currentMetadataTitle = trim((string) ($product['metadata']['title'] ?? ''));
        $currentMetadataDescription = trim((string) ($product['metadata']['description'] ?? ''));

        $description = trim((string) ($decoded['description'] ?? ''));
        $metadataTitle = trim((string) ($decoded['metadata_title'] ?? ''));
        $metadataDescription = trim((string) ($decoded['metadata_description'] ?? ''));

        if ($mode === 'description') {
            $metadataTitle = $currentMetadataTitle;
            $metadataDescription = $currentMetadataDescription;
        } elseif ($mode === 'seo') {
            $description = $currentDescription;
        }

        $usage = is_array($body['usage'] ?? null) ? $body['usage'] : [];

        return [
            'description' => $description,
            'metadata_title' => $metadataTitle,
            'metadata_description' => $metadataDescription,
            '_usage' => $usage,
            '_model' => $model,
        ];
    }

    public function generateImageAlt(array $product, array $image, array $settings = []): array
    {
        $apiKey = Config::get('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $model = Config::get('OPENAI_MODEL', 'gpt-5-mini');
        $reasoningEffort = Config::get('OPENAI_REASONING_EFFORT', 'low');
        $language = trim((string) ($settings['output_language'] ?? ''));
        if ($language === '') {
            $language = 'ar';
        }
        $globalInstructions = trim((string) ($settings['global_instructions'] ?? ''));
        $imageAltInstructions = trim((string) ($settings['image_alt_instructions'] ?? ''));

        $response = $this->httpClient->post(self::API_BASE . '/responses', [
            'model' => $model,
            'reasoning' => [
                'effort' => $reasoningEffort,
            ],
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => 'You are an expert ecommerce SEO specialist writing image ALT text. Write concise, natural, descriptive ALT text for product images. Return only plain ALT text without quotes, JSON, markdown, emojis, hashtags, or extra commentary.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Generate one ALT text in language={$language} as an SEO professional.\nRules:\n- Maximum length: 60 characters.\n- Mention the product clearly and naturally.\n- No keyword stuffing.\n- No promotional phrases.\n- Use letters, numbers and spaces only.\n- Return only ALT text.\n"
                                . $this->buildInstructionBlock('Global merchant instructions', $globalInstructions)
                                . $this->buildInstructionBlock('Image ALT instructions', $imageAltInstructions)
                                . "\nProduct name: " . (string) ($product['name'] ?? 'Product') . "\nCurrent image alt: " . (string) ($image['alt'] ?? ''),
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => (string) ($image['url'] ?? ''),
                        ],
                    ],
                ],
            ],
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ]);

        $body = $response['body'];
        $text = trim($this->extractText($body));

        if ($text === '') {
            throw new RuntimeException('OpenAI returned an empty image alt text.');
        }

        $text = $this->limitText($text, 60);

        $usage = is_array($body['usage'] ?? null) ? $body['usage'] : [];

        return [
            'alt' => $text,
            '_usage' => $usage,
            '_model' => $model,
        ];
    }

    public function generateStoreSeo(array $storeContext, array $currentSeo = [], array $settings = []): array
    {
        $apiKey = Config::get('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $model = Config::get('OPENAI_MODEL', 'gpt-5-mini');
        $reasoningEffort = Config::get('OPENAI_REASONING_EFFORT', 'low');
        $language = trim((string) ($settings['output_language'] ?? ''));
        if ($language === '') {
            $language = 'ar';
        }
        $globalInstructions = trim((string) ($settings['global_instructions'] ?? ''));
        $storeSeoInstructions = trim((string) ($settings['store_seo_instructions'] ?? ''));

        $response = $this->httpClient->post(self::API_BASE . '/responses', [
            'model' => $model,
            'reasoning' => [
                'effort' => $reasoningEffort,
            ],
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => 'You are a senior ecommerce SEO strategist. Return valid JSON only with keys: title, description, keywords. First analyze existing SEO and improve it (do not rewrite blindly). Infer the store niche from product names/topics. Keep claims factual and specific to the store context.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Generate homepage SEO settings in language={$language}. Return JSON only.\nRules:\n- Title should be around 35-65 characters and include core intent.\n- Description should be around 120-160 characters, compelling but factual.\n- Keywords should be 6-12 high-intent terms, comma-separated, no stuffing.\n- Reuse useful existing terms if they are relevant.\n- Reflect the actual store activity from products sample/topics.\n"
                                . $this->buildInstructionBlock('Global merchant instructions', $globalInstructions)
                                . $this->buildInstructionBlock('Store SEO instructions', $storeSeoInstructions)
                                . "\nStore context:\n" . json_encode([
                                    'store_name' => $storeContext['store_name'] ?? null,
                                    'merchant_id' => $storeContext['merchant_id'] ?? null,
                                    'store_url' => $storeContext['store_url'] ?? null,
                                    'products_count' => $storeContext['products_count'] ?? null,
                                    'products_sample' => $storeContext['products_sample'] ?? [],
                                    'product_topics' => $storeContext['product_topics'] ?? [],
                                    'existing_title' => $currentSeo['title'] ?? null,
                                    'existing_description' => $currentSeo['description'] ?? null,
                                    'existing_keywords' => $currentSeo['keywords'] ?? null,
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                        ],
                    ],
                ],
            ],
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ]);

        $body = $response['body'];
        $text = $this->extractText($body);

        if ($text === '') {
            throw new RuntimeException('OpenAI returned empty store SEO content.');
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('OpenAI returned invalid JSON for store SEO.');
        }

        $title = $this->limitText(trim((string) ($decoded['title'] ?? '')), 70);
        $description = $this->limitText(trim((string) ($decoded['description'] ?? '')), 300);
        $keywords = $this->normalizeKeywords((string) ($decoded['keywords'] ?? ''));

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            '_usage' => is_array($body['usage'] ?? null) ? $body['usage'] : [],
            '_model' => $model,
        ];
    }

    private function normalizeKeywords(string $keywords): string
    {
        $keywords = trim($keywords);
        if ($keywords === '') {
            return '';
        }

        $parts = preg_split('/[,،]+/u', $keywords) ?: [];
        $clean = [];

        foreach ($parts as $part) {
            $token = trim($part);
            if ($token === '') {
                continue;
            }
            $token = preg_replace('/\s+/u', ' ', $token) ?? $token;
            $lower = function_exists('mb_strtolower') ? mb_strtolower($token, 'UTF-8') : strtolower($token);
            if (!isset($clean[$lower])) {
                $clean[$lower] = $token;
            }
        }

        return implode(', ', array_slice(array_values($clean), 0, 12));
    }

    private function buildPrompt(array $product, array $settings): array
    {
        $language = trim((string) ($settings['output_language'] ?? ''));
        if ($language === '') {
            $language = 'ar';
        }
        $globalInstructions = trim((string) ($settings['global_instructions'] ?? ''));
        $productInstructions = trim((string) ($settings['product_description_instructions'] ?? ''));

        $productSummary = [
            'id' => $product['id'] ?? null,
            'name' => $product['name'] ?? null,
            'sku' => $product['sku'] ?? null,
            'price' => $product['price']['amount'] ?? $product['price'] ?? null,
            'quantity' => $product['quantity'] ?? null,
            'currency' => $product['currency'] ?? 'SAR',
            'description' => strip_tags((string) ($product['description'] ?? '')),
            'status' => $product['status'] ?? null,
        ];

        return [
            [
                'role' => 'system',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => 'You write ecommerce product descriptions for Salla merchants. Keep the copy accurate, persuasive, concise, and free of fabricated claims. Return only the final product description text without headings, JSON, or markdown fences.',
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => "Generate an improved product description in language={$language}. Focus on benefits, clarity, and conversion while staying faithful to the provided product data.\n"
                            . $this->buildInstructionBlock('Global merchant instructions', $globalInstructions)
                            . $this->buildInstructionBlock('Product description instructions', $productInstructions)
                            . "\nProduct:\n" . json_encode($productSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    ],
                ],
            ],
        ];
    }

    private function limitText(string $value, int $maxLength): string
    {
        $value = trim($value);
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

    private function buildContentPrompt(array $product, array $settings, string $mode): array
    {
        $language = trim((string) ($settings['output_language'] ?? ''));
        if ($language === '') {
            $language = 'ar';
        }
        $globalInstructions = trim((string) ($settings['global_instructions'] ?? ''));
        $productInstructions = trim((string) ($settings['product_description_instructions'] ?? ''));
        $metaTitleInstructions = trim((string) ($settings['meta_title_instructions'] ?? ''));
        $metaDescriptionInstructions = trim((string) ($settings['meta_description_instructions'] ?? ''));

        $productSummary = [
            'id' => $product['id'] ?? null,
            'name' => $product['name'] ?? null,
            'sku' => $product['sku'] ?? null,
            'price' => $product['price']['amount'] ?? $product['price'] ?? null,
            'quantity' => $product['quantity'] ?? null,
            'currency' => $product['currency'] ?? 'SAR',
            'description' => strip_tags((string) ($product['description'] ?? '')),
            'metadata_title' => $product['metadata']['title'] ?? null,
            'metadata_description' => $product['metadata']['description'] ?? null,
            'status' => $product['status'] ?? null,
        ];

        $modeInstruction = match ($mode) {
            'description' => 'Focus on rewriting the product description only. Keep metadata_title and metadata_description unchanged from the input when returning JSON.',
            'seo' => 'Focus on generating only SEO metadata. Keep the description unchanged from the input when returning JSON.',
            default => 'Generate both an improved product description and improved SEO metadata.',
        };

        return [
            [
                'role' => 'system',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => 'You write ecommerce product copy for Salla merchants. Return valid JSON only with keys: description, metadata_title, metadata_description. Keep claims accurate, make metadata_title concise, and keep metadata_description SEO-friendly within about 160 characters.',
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => "Generate improved product content in language={$language}. {$modeInstruction} Return JSON only.\n"
                            . $this->buildInstructionBlock('Global merchant instructions', $globalInstructions)
                            . $this->buildInstructionBlock('Product description instructions', $productInstructions)
                            . $this->buildInstructionBlock('Meta title instructions', $metaTitleInstructions)
                            . $this->buildInstructionBlock('Meta description instructions', $metaDescriptionInstructions)
                            . "\nProduct:\n" . json_encode($productSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    ],
                ],
            ],
        ];
    }

    private function buildInstructionBlock(string $label, string $instructions): string
    {
        $instructions = trim($instructions);
        if ($instructions === '') {
            return '';
        }

        return "\n{$label}:\n{$instructions}\n";
    }

    private function extractText(array $body): string
    {
        if (isset($body['output_text']) && is_string($body['output_text'])) {
            return trim($body['output_text']);
        }

        $chunks = [];

        foreach (($body['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (isset($content['text']) && is_string($content['text'])) {
                    $chunks[] = $content['text'];
                }
            }
        }

        return trim(implode("\n", $chunks));
    }
}
