<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$seeds = [
    'salla seo',
    'seo salla',
    'salla app seo',
    'salla google',
    'seo app',
    'product seo salla',
    'ecommerce seo saudi',
];

$client = new \App\Services\DataForSeoClient();
$out = [
    'ok' => true,
    'generated_at' => date('c'),
    'seeds' => [],
];

foreach ($seeds as $seed) {
    try {
        $r = $client->keywordOverview($seed, 'desktop', 'sa', 'en');
        $metrics = (array) ($r['metrics'] ?? []);
        $related = array_slice((array) ($r['related_keywords'] ?? []), 0, 8);
        $suggestions = array_slice((array) ($r['keyword_suggestions'] ?? []), 0, 8);

        $out['seeds'][] = [
            'keyword' => $seed,
            'search_volume' => (int) ($metrics['search_volume'] ?? 0),
            'competition_index' => (float) ($metrics['competition'] ?? 0),
            'competition_level' => (string) ($metrics['competition_level'] ?? ''),
            'cpc' => (float) ($metrics['cpc'] ?? 0),
            'related' => array_map(static function (array $item): array {
                return [
                    'keyword' => (string) ($item['keyword'] ?? ''),
                    'search_volume' => (int) ($item['search_volume'] ?? 0),
                    'competition' => (float) ($item['competition'] ?? 0),
                    'cpc' => (float) ($item['cpc'] ?? 0),
                ];
            }, $related),
            'suggestions' => array_map(static function (array $item): array {
                return [
                    'keyword' => (string) ($item['keyword'] ?? ''),
                    'search_volume' => (int) ($item['search_volume'] ?? 0),
                    'competition' => (float) ($item['competition'] ?? 0),
                    'cpc' => (float) ($item['cpc'] ?? 0),
                ];
            }, $suggestions),
            'usage' => (array) ($r['_usage'] ?? []),
        ];
    } catch (\Throwable $e) {
        $out['ok'] = false;
        $out['seeds'][] = [
            'keyword' => $seed,
            'error' => $e->getMessage(),
        ];
    }
}

$target = __DIR__ . '/dataforseo-keyword-research-en.json';
file_put_contents(
    $target,
    json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);

echo json_encode([
    'saved' => $target,
    'ok' => $out['ok'],
    'count' => count($out['seeds']),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
