<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SitemapService
{
    private const MAX_SITEMAPS = 8;
    private const MAX_URLS = 1500;

    public function __construct(
        private readonly HttpClient $httpClient = new HttpClient()
    ) {
    }

    /**
     * @return array{
     *   sitemap_url:string,
     *   links:array<int,array{url:string,title:string,type:string}>,
     *   links_count:int,
     *   fetched_at:string
     * }
     */
    public function fetchAndParse(string $sitemapUrl): array
    {
        $normalized = $this->normalizeSitemapUrl($sitemapUrl);
        if ($normalized === '') {
            throw new RuntimeException('رابط السايت ماب غير صالح.');
        }

        $queue = [$normalized];
        $visited = [];
        $urlRows = [];
        $baseHost = (string) parse_url($normalized, PHP_URL_HOST);
        $sitemapCounter = 0;

        while ($queue !== [] && $sitemapCounter < self::MAX_SITEMAPS && count($urlRows) < self::MAX_URLS) {
            $current = array_shift($queue);
            if (!is_string($current) || $current === '' || isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            $sitemapCounter++;

            $xml = $this->downloadXml($current);
            [$childSitemaps, $urls] = $this->parseSitemapXml($xml);

            foreach ($childSitemaps as $child) {
                if (count($visited) >= self::MAX_SITEMAPS) {
                    break;
                }
                $child = $this->normalizeSitemapUrl($child);
                if ($child !== '' && !isset($visited[$child])) {
                    $queue[] = $child;
                }
            }

            foreach ($urls as $url) {
                if (count($urlRows) >= self::MAX_URLS) {
                    break;
                }
                $normalizedUrl = $this->normalizeSiteUrl($url);
                if ($normalizedUrl === '') {
                    continue;
                }

                $host = (string) parse_url($normalizedUrl, PHP_URL_HOST);
                if ($baseHost !== '' && $host !== '' && strcasecmp($host, $baseHost) !== 0) {
                    continue;
                }

                $urlRows[$normalizedUrl] = [
                    'url' => $normalizedUrl,
                    'title' => $this->buildTitleFromUrl($normalizedUrl),
                    'type' => $this->inferTypeFromUrl($normalizedUrl),
                ];
            }
        }

        return [
            'sitemap_url' => $normalized,
            'links' => array_values($urlRows),
            'links_count' => count($urlRows),
            'fetched_at' => date(DATE_ATOM),
        ];
    }

    private function downloadXml(string $url): string
    {
        $response = $this->httpClient->get($url, [
            'Accept' => 'application/xml,text/xml,*/*',
        ]);

        $raw = (string) ($response['body']['raw'] ?? '');
        if (trim($raw) === '') {
            throw new RuntimeException('تعذر قراءة ملف السايت ماب، تأكد أن الرابط مباشر.');
        }

        return $raw;
    }

    /**
     * @return array{0:array<int,string>,1:array<int,string>}
     */
    private function parseSitemapXml(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            throw new RuntimeException('تعذر تحليل XML للسايت ماب.');
        }

        $childSitemaps = [];
        $urls = [];

        $root = strtolower($doc->getName());
        if ($root === 'sitemapindex') {
            foreach ($doc->sitemap as $sitemap) {
                $loc = trim((string) ($sitemap->loc ?? ''));
                if ($loc !== '') {
                    $childSitemaps[] = $loc;
                }
            }
        } elseif ($root === 'urlset') {
            foreach ($doc->url as $url) {
                $loc = trim((string) ($url->loc ?? ''));
                if ($loc !== '') {
                    $urls[] = $loc;
                }
            }
        } else {
            // Try generic extraction as fallback.
            foreach ($doc->xpath('//loc') ?: [] as $locNode) {
                $loc = trim((string) $locNode);
                if ($loc !== '') {
                    $urls[] = $loc;
                }
            }
        }

        return [$childSitemaps, $urls];
    }

    private function normalizeSitemapUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!str_contains($url, '://')) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '') {
            return '';
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '') {
            $path = '/sitemap.xml';
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $scheme . '://' . $host . $path . $query;
    }

    private function normalizeSiteUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!str_contains($url, '://')) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '') {
            return '';
        }

        $path = (string) ($parts['path'] ?? '/');
        $path = preg_replace('~/+~', '/', $path) ?? $path;
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $scheme . '://' . $host . $path . $query;
    }

    private function buildTitleFromUrl(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');
        if ($path === '') {
            return 'الصفحة الرئيسية';
        }

        $segments = explode('/', $path);
        $last = (string) end($segments);
        $last = urldecode($last);
        $last = preg_replace('/[-_]+/u', ' ', $last) ?? $last;
        $last = preg_replace('/\s+/u', ' ', $last) ?? $last;
        $last = trim($last);
        if ($last === '') {
            return 'رابط داخلي';
        }

        return $last;
    }

    private function inferTypeFromUrl(string $url): string
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (str_contains($path, '/p') || preg_match('~/(product|products)/~', $path) === 1) {
            return 'product';
        }
        if (preg_match('~/(category|categories|c)/~', $path) === 1) {
            return 'category';
        }

        return 'page';
    }
}

