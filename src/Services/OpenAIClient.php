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
        $language = $settings['language'] ?? 'ar';

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
                            'text' => 'You write concise, descriptive, SEO-friendly alt text for ecommerce product images. Return only the final alt text without quotes, JSON, or markdown.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Generate alt text in language={$language}. Mention the product accurately, avoid keyword stuffing, and keep it concise.\n\nProduct name: " . (string) ($product['name'] ?? 'Product') . "\nCurrent image alt: " . (string) ($image['alt'] ?? ''),
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
        $language = $settings['language'] ?? 'ar';
        $tone = $settings['tone'] ?? 'احترافي مقنع';

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
                            'text' => 'You write ecommerce homepage SEO for online stores. Return valid JSON only with keys: title, description, keywords. Keep the title concise, the description SEO-friendly around 140-170 characters, and keywords as a short comma-separated list. Do not fabricate unverifiable claims.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Generate homepage SEO settings in language={$language} with tone={$tone}. Return JSON only.\n\nStore context:\n" . json_encode([
                                'store_name' => $storeContext['store_name'] ?? null,
                                'merchant_id' => $storeContext['merchant_id'] ?? null,
                                'store_url' => $storeContext['store_url'] ?? null,
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

        return [
            'title' => trim((string) ($decoded['title'] ?? '')),
            'description' => trim((string) ($decoded['description'] ?? '')),
            'keywords' => trim((string) ($decoded['keywords'] ?? '')),
            '_usage' => is_array($body['usage'] ?? null) ? $body['usage'] : [],
            '_model' => $model,
        ];
    }

    private function buildPrompt(array $product, array $settings): array
    {
        $tone = $settings['tone'] ?? 'احترافي مقنع';
        $language = $settings['language'] ?? 'ar';

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
                        'text' => "Generate an improved product description in language={$language} with tone={$tone}. Focus on benefits, clarity, and conversion while staying faithful to the provided product data.\n\nProduct:\n" . json_encode($productSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    ],
                ],
            ],
        ];
    }

    private function buildContentPrompt(array $product, array $settings, string $mode): array
    {
        $tone = $settings['tone'] ?? 'احترافي مقنع';
        $language = $settings['language'] ?? 'ar';

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
                        'text' => "Generate improved product content in language={$language} with tone={$tone}. {$modeInstruction} Return JSON only.\n\nProduct:\n" . json_encode($productSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    ],
                ],
            ],
        ];
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
