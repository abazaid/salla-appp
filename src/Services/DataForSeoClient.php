<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use RuntimeException;

final class DataForSeoClient
{
    private const BASE_URL = 'https://api.dataforseo.com/v3';

    private HttpClient $http;
    private string $login;
    private string $password;

    public function __construct(?HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
        $this->login = (string) Config::get('DATAFORSEO_LOGIN', Config::get('DATAFORSEO_USERNAME', ''));
        $this->password = (string) Config::get('DATAFORSEO_PASSWORD', '');
    }

    public function keywordOverview(string $keyword, string $device = 'desktop'): array
    {
        $this->assertCredentials();

        $normalizedKeyword = trim($keyword);
        if ($normalizedKeyword === '') {
            throw new RuntimeException('Keyword is required.');
        }

        $normalizedDevice = $this->normalizeDevice($device);

        $volumeTask = $this->post('/keywords_data/google_ads/search_volume/live', [[
            'keywords' => [$normalizedKeyword],
            'location_name' => 'Saudi Arabia',
            'language_name' => 'Arabic',
            'search_partners' => false,
        ]]);

        $keywordMetrics = $this->extractKeywordMetrics($volumeTask, $normalizedKeyword);

        $serpTask = $this->post('/serp/google/organic/live/advanced', [[
            'keyword' => $normalizedKeyword,
            'location_name' => 'Saudi Arabia',
            'language_name' => 'Arabic',
            'device' => $normalizedDevice,
            'os' => $normalizedDevice === 'mobile' ? 'android' : 'windows',
            'depth' => 10,
        ]]);

        $serp = $this->extractSerp($serpTask);

        return [
            'keyword' => $normalizedKeyword,
            'country' => 'sa',
            'country_name' => 'السعودية',
            'device' => $normalizedDevice,
            'metrics' => $keywordMetrics['metrics'],
            'trend' => $keywordMetrics['trend'],
            'serp' => $serp,
            'fetched_at' => date(DATE_ATOM),
        ];
    }

    public function domainOverview(string $domain, string $device = 'desktop'): array
    {
        $this->assertCredentials();

        $normalizedDomain = $this->normalizeDomain($domain);
        if ($normalizedDomain === '') {
            throw new RuntimeException('Domain is required.');
        }

        $normalizedDevice = $this->normalizeDevice($device);

        $overviewTask = $this->post('/dataforseo_labs/google/domain_rank_overview/live', [[
            'target' => $normalizedDomain,
            'location_name' => 'Saudi Arabia',
            'language_name' => 'Arabic',
            'item_types' => ['organic', 'paid'],
        ]]);

        $competitorsTask = $this->post('/dataforseo_labs/google/competitors_domain/live', [[
            'target' => $normalizedDomain,
            'location_name' => 'Saudi Arabia',
            'language_name' => 'Arabic',
            'exclude_top_domains' => true,
            'max_rank_group' => $normalizedDevice === 'mobile' ? 20 : 10,
            'limit' => 12,
        ]]);

        $keywordsTask = $this->post('/dataforseo_labs/google/ranked_keywords/live', [[
            'target' => $normalizedDomain,
            'location_name' => 'Saudi Arabia',
            'language_name' => 'Arabic',
            'limit' => 12,
            'order_by' => ['keyword_data.keyword_info.search_volume,desc'],
            'filters' => [
                ['ranked_serp_element.serp_item.rank_group', '<=', $normalizedDevice === 'mobile' ? 30 : 20],
                'and',
                ['ranked_serp_element.serp_item.type', '<>', 'paid'],
            ],
        ]]);

        return [
            'domain' => $normalizedDomain,
            'country' => 'sa',
            'country_name' => 'السعودية',
            'device' => $normalizedDevice,
            'overview' => $this->extractDomainOverview($overviewTask),
            'competitors' => $this->extractDomainCompetitors($competitorsTask),
            'top_keywords' => $this->extractDomainKeywords($keywordsTask),
            'fetched_at' => date(DATE_ATOM),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     */
    private function post(string $path, array $payload): array
    {
        $auth = base64_encode($this->login . ':' . $this->password);
        $response = $this->http->post(
            self::BASE_URL . $path,
            $payload,
            [
                'Authorization' => 'Basic ' . $auth,
            ]
        );

        $body = (array) ($response['body'] ?? []);
        $statusCode = (int) ($body['status_code'] ?? 0);

        if ($statusCode !== 20000) {
            $message = (string) ($body['status_message'] ?? 'DataForSEO request failed.');
            throw new RuntimeException($message);
        }

        return $body;
    }

    private function assertCredentials(): void
    {
        if ($this->login === '' || $this->password === '') {
            throw new RuntimeException('DataForSEO credentials are missing. Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD in .env');
        }
    }

    private function normalizeDevice(string $device): string
    {
        return $device === 'mobile' ? 'mobile' : 'desktop';
    }

    private function normalizeDomain(string $domain): string
    {
        $value = trim($domain);
        if ($value === '') {
            return '';
        }

        if (!str_contains($value, '://')) {
            $value = 'https://' . $value;
        }

        $host = (string) parse_url($value, PHP_URL_HOST);
        $host = strtolower(trim($host));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $host = trim($host, '.');

        if ($host === '' || !str_contains($host, '.')) {
            return '';
        }

        return $host;
    }

    private function extractKeywordMetrics(array $response, string $keyword): array
    {
        $tasks = (array) ($response['tasks'] ?? []);
        $task = (array) ($tasks[0] ?? []);
        $resultSet = (array) ($task['result'] ?? []);
        $firstResult = (array) ($resultSet[0] ?? []);
        $items = (array) ($firstResult['items'] ?? []);
        $item = (array) ($items[0] ?? []);

        if ($item === []) {
            return [
                'metrics' => [
                    'search_volume' => 0,
                    'competition' => 0,
                    'competition_level' => null,
                    'cpc' => 0.0,
                    'low_bid' => 0.0,
                    'high_bid' => 0.0,
                    'monthly_searches_count' => 0,
                ],
                'trend' => [],
            ];
        }

        $monthly = array_values(array_map(static function (array $row): array {
            return [
                'year' => (int) ($row['year'] ?? 0),
                'month' => (int) ($row['month'] ?? 0),
                'search_volume' => (int) ($row['search_volume'] ?? 0),
            ];
        }, array_filter((array) ($item['monthly_searches'] ?? []), static fn ($row): bool => is_array($row))));

        usort($monthly, static function (array $a, array $b): int {
            return [$a['year'], $a['month']] <=> [$b['year'], $b['month']];
        });

        return [
            'metrics' => [
                'keyword' => (string) ($item['keyword'] ?? $keyword),
                'search_volume' => (int) ($item['search_volume'] ?? 0),
                'competition' => (float) ($item['competition'] ?? 0),
                'competition_level' => isset($item['competition_level']) ? (string) $item['competition_level'] : null,
                'cpc' => (float) ($item['cpc'] ?? 0),
                'low_bid' => (float) ($item['low_top_of_page_bid'] ?? 0),
                'high_bid' => (float) ($item['high_top_of_page_bid'] ?? 0),
                'monthly_searches_count' => count($monthly),
            ],
            'trend' => $monthly,
        ];
    }

    private function extractSerp(array $response): array
    {
        $tasks = (array) ($response['tasks'] ?? []);
        $task = (array) ($tasks[0] ?? []);
        $results = (array) ($task['result'] ?? []);
        $firstResult = (array) ($results[0] ?? []);
        $items = array_values(array_filter((array) ($firstResult['items'] ?? []), static function ($item): bool {
            return is_array($item) && in_array((string) ($item['type'] ?? ''), ['organic', 'answer_box', 'people_also_ask', 'images'], true);
        }));

        $mapped = array_map(static function (array $item): array {
            return [
                'type' => (string) ($item['type'] ?? ''),
                'rank_group' => (int) ($item['rank_group'] ?? 0),
                'rank_absolute' => (int) ($item['rank_absolute'] ?? 0),
                'title' => (string) ($item['title'] ?? ''),
                'url' => (string) ($item['url'] ?? ''),
                'domain' => (string) ($item['domain'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
            ];
        }, $items);

        return [
            'se_type' => (string) ($firstResult['se_type'] ?? 'google'),
            'location_name' => (string) ($firstResult['location_name'] ?? 'Saudi Arabia'),
            'language_name' => (string) ($firstResult['language_name'] ?? 'Arabic'),
            'total_count' => (int) ($firstResult['items_count'] ?? count($mapped)),
            'items' => array_slice($mapped, 0, 10),
        ];
    }

    private function extractDomainOverview(array $response): array
    {
        $tasks = (array) ($response['tasks'] ?? []);
        $task = (array) ($tasks[0] ?? []);
        $results = (array) ($task['result'] ?? []);
        $firstResult = (array) ($results[0] ?? []);
        $metrics = (array) ($firstResult['metrics'] ?? []);
        $organic = (array) ($metrics['organic'] ?? []);
        $paid = (array) ($metrics['paid'] ?? []);

        return [
            'organic' => [
                'keywords_count' => (int) ($organic['count'] ?? 0),
                'traffic' => (float) ($organic['etv'] ?? 0),
                'traffic_cost' => (float) ($organic['estimated_paid_traffic_cost'] ?? 0),
                'new' => (int) ($organic['is_new'] ?? 0),
                'up' => (int) ($organic['is_up'] ?? 0),
                'down' => (int) ($organic['is_down'] ?? 0),
                'lost' => (int) ($organic['is_lost'] ?? 0),
                'positions' => [
                    'top_3' => (int) (($organic['pos_1'] ?? 0) + ($organic['pos_2_3'] ?? 0)),
                    'top_10' => (int) (($organic['pos_1'] ?? 0) + ($organic['pos_2_3'] ?? 0) + ($organic['pos_4_10'] ?? 0)),
                    'top_20' => (int) (
                        ($organic['pos_1'] ?? 0) +
                        ($organic['pos_2_3'] ?? 0) +
                        ($organic['pos_4_10'] ?? 0) +
                        ($organic['pos_11_20'] ?? 0)
                    ),
                    'top_100' => (int) (
                        ($organic['pos_1'] ?? 0) +
                        ($organic['pos_2_3'] ?? 0) +
                        ($organic['pos_4_10'] ?? 0) +
                        ($organic['pos_11_20'] ?? 0) +
                        ($organic['pos_21_30'] ?? 0) +
                        ($organic['pos_31_40'] ?? 0) +
                        ($organic['pos_41_50'] ?? 0) +
                        ($organic['pos_51_60'] ?? 0) +
                        ($organic['pos_61_70'] ?? 0) +
                        ($organic['pos_71_80'] ?? 0) +
                        ($organic['pos_81_90'] ?? 0) +
                        ($organic['pos_91_100'] ?? 0)
                    ),
                ],
            ],
            'paid' => [
                'keywords_count' => (int) ($paid['count'] ?? 0),
                'traffic' => (float) ($paid['etv'] ?? 0),
                'traffic_cost' => (float) ($paid['estimated_paid_traffic_cost'] ?? 0),
                'new' => (int) ($paid['is_new'] ?? 0),
                'up' => (int) ($paid['is_up'] ?? 0),
                'down' => (int) ($paid['is_down'] ?? 0),
                'lost' => (int) ($paid['is_lost'] ?? 0),
            ],
        ];
    }

    private function extractDomainCompetitors(array $response): array
    {
        $tasks = (array) ($response['tasks'] ?? []);
        $task = (array) ($tasks[0] ?? []);
        $results = (array) ($task['result'] ?? []);
        $firstResult = (array) ($results[0] ?? []);
        $items = array_values(array_filter((array) ($firstResult['items'] ?? []), 'is_array'));

        $mapped = array_map(static function (array $item): array {
            $fullMetrics = (array) ($item['full_domain_metrics'] ?? []);
            $organic = (array) ($fullMetrics['organic'] ?? []);

            return [
                'domain' => (string) ($item['domain'] ?? ''),
                'intersections' => (int) ($item['intersections'] ?? 0),
                'avg_position' => (float) ($item['avg_position'] ?? 0),
                'organic_keywords' => (int) ($organic['count'] ?? 0),
                'organic_traffic' => (float) ($organic['etv'] ?? 0),
                'organic_cost' => (float) ($organic['estimated_paid_traffic_cost'] ?? 0),
            ];
        }, $items);

        return array_slice($mapped, 0, 12);
    }

    private function extractDomainKeywords(array $response): array
    {
        $tasks = (array) ($response['tasks'] ?? []);
        $task = (array) ($tasks[0] ?? []);
        $results = (array) ($task['result'] ?? []);
        $firstResult = (array) ($results[0] ?? []);
        $items = array_values(array_filter((array) ($firstResult['items'] ?? []), 'is_array'));

        $mapped = array_map(static function (array $item): array {
            $keywordData = (array) ($item['keyword_data'] ?? []);
            $keywordInfo = (array) ($keywordData['keyword_info'] ?? []);
            $rankedSerp = (array) ($item['ranked_serp_element'] ?? []);
            $serpItem = (array) ($rankedSerp['serp_item'] ?? []);

            return [
                'keyword' => (string) ($keywordData['keyword'] ?? ''),
                'position' => (int) ($serpItem['rank_group'] ?? 0),
                'search_volume' => (int) ($keywordInfo['search_volume'] ?? 0),
                'cpc' => (float) ($keywordInfo['cpc'] ?? 0),
                'competition' => (float) ($keywordInfo['competition'] ?? 0),
                'intent' => (string) (($keywordData['search_intent_info']['main_intent'] ?? '')),
            ];
        }, $items);

        return array_slice($mapped, 0, 12);
    }
}

