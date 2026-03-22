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
        ], 90);

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
        ], 90);

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
            $description = $this->ensureSallaHtmlDescription($description);
            $metadataTitle = $currentMetadataTitle;
            $metadataDescription = $currentMetadataDescription;
        } elseif ($mode === 'seo') {
            $description = $currentDescription;
        } else {
            $description = $this->ensureSallaHtmlDescription($description);
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
                            'text' => "Generate one ALT text in language={$language} as an SEO professional.\nRules:\n- Length target: 55-70 characters.\n- Mention the product clearly and naturally.\n- Must be a complete, readable phrase (not cut off).\n- No keyword stuffing.\n- No promotional phrases.\n- Use letters, numbers and spaces only.\n- Return only ALT text.\n"
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
        ], 90);

        $body = $response['body'];
        $text = trim($this->extractText($body));

        if ($text === '') {
            throw new RuntimeException('OpenAI returned an empty image alt text.');
        }

        $text = $this->limitAtWordBoundary($text, 70);

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
        ], 90);

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
                        'text' => 'You write ecommerce product descriptions for Salla merchants. Keep the copy accurate and factual. Return only clean HTML that can be pasted directly into Salla editor. Use only tags: <h2>, <p>, <ul>, <li>, <strong>, <a>. Never use markdown, never write "H2:" labels.',
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                            'text' => "Generate an improved product description in language={$language}. Focus on benefits, clarity, and conversion while staying faithful to the provided product data.\nRules:\n- Output must be valid HTML sections for Salla editor.\n- Use <h2> for section titles, <p> for paragraphs, and <ul><li> for feature lists.\n- Use <a href=\"...\">...</a> only for internal links if provided.\n- No markdown, no plain labels like H2:, no fabricated specs.\n"
                            . $this->buildInstructionBlock('Internal links rule', $this->buildInternalLinksPromptBlock($product, $settings, true))
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

    private function limitAtWordBoundary(string $value, int $maxLength): string
    {
        $value = trim($value);
        if ($value === '' || $maxLength <= 0) {
            return '';
        }

        $limited = $this->limitText($value, $maxLength);
        if ($limited === $value) {
            return $limited;
        }

        $chunks = preg_split('/\s+/u', $limited) ?: [];
        if (count($chunks) <= 1) {
            return $limited;
        }

        array_pop($chunks);
        $wordSafe = trim(implode(' ', $chunks));

        return $wordSafe !== '' ? $wordSafe : $limited;
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
                        'text' => 'You write ecommerce product copy for Salla merchants. Return valid JSON only with keys: description, metadata_title, metadata_description. Keep claims accurate. Description must be clean Salla-ready HTML (only <h2>, <p>, <ul>, <li>, <strong>, <a>) with no markdown and no plain "H2:" labels. metadata_title concise. metadata_description SEO-friendly within about 160 characters.',
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                            'text' => "Generate improved product content in language={$language}. {$modeInstruction} Return JSON only.\nRules for description field (if generated):\n- Must be valid HTML sections.\n- Use <h2> titles, <p> paragraphs, <ul><li> for bullets.\n- Use <a href=\"...\">...</a> only for internal links if provided.\n- Do not return markdown, do not use plain labels like H2:.\n"
                            . $this->buildInstructionBlock('Internal links rule', $this->buildInternalLinksPromptBlock($product, $settings, $mode !== 'seo'))
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

    /**
     * @return array<int, array{url:string,title:string,type:string,score:int}>
     */
    private function pickRelevantSitemapLinks(array $product, array $settings, int $max = 3): array
    {
        $rows = is_array($settings['sitemap_links_cache'] ?? null)
            ? (array) $settings['sitemap_links_cache']
            : [];

        if ($rows === [] || $max <= 0) {
            return [];
        }

        $tokens = $this->extractKeywordsFromProduct($product);
        $currentUrl = trim((string) ($product['url'] ?? ($product['urls']['customer'] ?? '')));
        $scored = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $url = trim((string) ($row['url'] ?? ''));
            if ($url === '' || ($currentUrl !== '' && strcasecmp($currentUrl, $url) === 0)) {
                continue;
            }

            $type = trim((string) ($row['type'] ?? 'page'));
            if (!in_array($type, ['product', 'category', 'page'], true)) {
                $type = 'page';
            }
            $title = trim((string) ($row['title'] ?? ''));
            $haystack = $this->normalizeComparableText($title . ' ' . $url);

            $score = 0;
            if ($type === 'category') {
                $score += 20;
            } elseif ($type === 'product') {
                $score += 12;
            } else {
                $score += 6;
            }

            foreach ($tokens as $token) {
                $token = is_string($token) ? trim($token) : (string) $token;
                if ($token !== '' && $this->containsText($haystack, $token)) {
                    $length = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
                    $score += $length >= 5 ? 8 : 4;
                }
            }

            if ($score <= 6) {
                continue;
            }

            $scored[] = [
                'url' => $url,
                'title' => $title,
                'type' => $type,
                'score' => $score,
            ];
        }

        if ($scored === []) {
            return [];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $deduped = [];
        $seen = [];
        foreach ($scored as $row) {
            $key = strtolower($row['url']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $deduped[] = $row;
            if (count($deduped) >= $max) {
                break;
            }
        }

        return $deduped;
    }

    /**
     * @return array<int, string>
     */
    private function extractKeywordsFromProduct(array $product): array
    {
        $source = trim(implode(' ', [
            (string) ($product['name'] ?? ''),
            strip_tags((string) ($product['description'] ?? '')),
            (string) ($product['metadata']['title'] ?? ''),
            (string) ($product['metadata']['description'] ?? ''),
        ]));

        if ($source === '') {
            return [];
        }

        $source = $this->normalizeComparableText($source);
        $parts = preg_split('/\s+/u', $source) ?: [];
        $stopWords = [
            'the', 'and', 'for', 'with', 'this', 'that', 'from', 'your',
            'على', 'من', 'إلى', 'في', 'عن', 'هذا', 'هذه', 'مع', 'أو', 'و', 'ثم', 'الى'
        ];
        $stop = array_fill_keys($stopWords, true);
        $tokens = [];

        foreach ($parts as $part) {
            $token = trim($part);
            $length = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
            if ($token === '' || $length < 3) {
                continue;
            }
            // Skip pure numeric tokens (e.g. sizes like 36/110) to avoid noisy matching.
            if (preg_match('/^\d+$/', $token) === 1) {
                continue;
            }
            if (isset($stop[$token])) {
                continue;
            }
            $tokens[$token] = true;
            if (count($tokens) >= 18) {
                break;
            }
        }

        return array_keys($tokens);
    }

    private function normalizeComparableText(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            $value = mb_strtolower($value, 'UTF-8');
        } else {
            $value = strtolower($value);
        }
        $value = preg_replace('/[^\p{L}\p{N}\s\-\/]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function buildInternalLinksPromptBlock(array $product, array $settings, bool $descriptionMode): string
    {
        if (!$descriptionMode) {
            return '';
        }

        $links = $this->pickRelevantSitemapLinks($product, $settings, 3);
        if ($links === []) {
            return 'If no internal links list is provided, skip internal links and continue normally.';
        }

        $rows = [];
        foreach ($links as $index => $link) {
            $label = $link['title'] !== '' ? $link['title'] : ('Link ' . ($index + 1));
            $rows[] = '- ' . $label . ' | ' . $link['url'] . ' | type=' . $link['type'];
        }

        return "Use exactly 2-3 relevant internal links from this list inside the description body (never external links, never the same link repeated):\n"
            . implode("\n", $rows)
            . "\nPlace links naturally in suitable sections using HTML anchor tags.";
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

    private function ensureSallaHtmlDescription(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/<(h2|p|ul|li|strong)\b/i', $value) === 1) {
            return $value;
        }

        $lines = preg_split('/\R/u', $value) ?: [];
        $html = [];
        $inList = false;

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^H2\s*[:\-]\s*(.+)$/iu', $line, $matches) === 1 || preg_match('/^##\s*(.+)$/u', $line, $matches) === 1) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $html[] = '<h2>' . $this->escapeHtml(trim((string) $matches[1])) . '</h2>';
                continue;
            }

            if (preg_match('/^[-*•]\s+(.+)$/u', $line, $matches) === 1 || preg_match('/^\d+[\.\)\-]\s+(.+)$/u', $line, $matches) === 1) {
                if (!$inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>' . $this->escapeHtml(trim((string) $matches[1])) . '</li>';
                continue;
            }

            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }

            $html[] = '<p>' . $this->escapeHtml($line) . '</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return trim(implode("\n", $html));
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function containsText(mixed $haystack, mixed $needle): bool
    {
        $haystackText = is_string($haystack) ? $haystack : (string) $haystack;
        $needleText = is_string($needle) ? $needle : (string) $needle;
        if ($needleText === '') {
            return false;
        }

        return strpos($haystackText, $needleText) !== false;
    }
}
