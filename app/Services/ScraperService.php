<?php

namespace App\Services;

use App\Models\ContentSource;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class ScraperService
{
    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
    ];

    public function scrape(ContentSource $source): array
    {
        $rules = $this->resolveRules($source);
        $mode = $rules['crawl_mode'] ?? $source->crawl_mode ?? 'auto';
        $seedUrl = $rules['sitemap_url'] ?? $source->sitemap_url ?? $source->feed_url ?? $source->source_url;

        if ($mode === 'rss' || $this->looksLikeFeed($this->peekBody($seedUrl, $source)['body'] ?? '')) {
            return $this->crawlFeed($source, $seedUrl, $rules);
        }

        if ($mode === 'sitemap' || ! empty($seedUrl) && str_contains((string) $seedUrl, 'sitemap')) {
            return $this->crawlSitemap($source, $seedUrl, $rules);
        }

        if ($mode === 'pagination' || ! empty($rules['next_page_xpath']) || ! empty($source->next_page_xpath)) {
            return $this->crawlPagination($source, $source->source_url, $rules);
        }

        return $this->crawlPagination($source, $source->source_url, $rules);
    }

    protected function crawlFeed(ContentSource $source, string $feedUrl, array $rules): array
    {
        $response = $this->fetch($source, $feedUrl);
        $xml = @simplexml_load_string($response['body'], SimpleXMLElement::class, LIBXML_NOCDATA);

        if (! $xml) {
            return [];
        }

        $items = [];
        $feedItems = $xml->channel->item ?? $xml->entry ?? [];
        $limit = $source->max_items > 0 ? $source->max_items : 50;

        foreach ($feedItems as $feedItem) {
            if (count($items) >= $limit) {
                break;
            }

            $articleUrl = $this->resolveUrl($feedUrl, $this->extractFeedLink($feedItem));
            if ($articleUrl === '') {
                continue;
            }

            if ($this->shouldSkipUrl($articleUrl, $rules, $source->source_url)) {
                continue;
            }

            $articleResponse = $this->fetch($source, $articleUrl);

            $items[] = $this->extractArticleItem(
                source: $source,
                sourceUrl: $articleUrl,
                html: $articleResponse['body'],
                httpStatus: $articleResponse['status'],
                fallbackTitle: $this->cleanText((string) ($feedItem->title ?? '')),
                fallbackDescription: $this->cleanText((string) ($feedItem->description ?? $feedItem->summary ?? $feedItem->content ?? '')),
                fallbackImage: $this->extractFeedImage($feedItem),
                fallbackAuthor: $this->extractFeedAuthor($feedItem),
                fallbackPublishedAt: $this->extractFeedDate($feedItem),
                rawPayload: [
                    'feed' => json_decode(json_encode($feedItem), true),
                    'link' => $articleUrl,
                ],
                fingerprintSeed: $articleUrl,
                rules: $rules
            );
        }

        return $items;
    }

    protected function crawlSitemap(ContentSource $source, string $sitemapUrl, array $rules): array
    {
        $visitedSitemaps = [];
        $urls = $this->extractSitemapUrls($source, $sitemapUrl, $visitedSitemaps, $rules);
        $items = [];
        $limit = $source->max_items > 0 ? $source->max_items : 50;

        foreach ($urls as $url) {
            if (count($items) >= $limit) {
                break;
            }

            $articleResponse = $this->fetch($source, $url);

            $items[] = $this->extractArticleItem(
                source: $source,
                sourceUrl: $url,
                html: $articleResponse['body'],
                httpStatus: $articleResponse['status'],
                fallbackTitle: null,
                fallbackDescription: null,
                fallbackImage: null,
                fallbackAuthor: null,
                fallbackPublishedAt: null,
                rawPayload: [
                    'sitemap' => $sitemapUrl,
                    'link' => $url,
                ],
                fingerprintSeed: $url,
                rules: $rules
            );
        }

        return $items;
    }

    protected function crawlPagination(ContentSource $source, string $startUrl, array $rules): array
    {
        $items = [];
        $visitedPages = [];
        $queue = [$startUrl];
        $maxPages = $source->max_pages > 0 ? $source->max_pages : 50;
        $maxDepth = $source->max_depth > 0 ? $source->max_depth : 3;
        $pageCount = 0;

        while (! empty($queue) && $pageCount < $maxPages) {
            $current = array_shift($queue);

            if (isset($visitedPages[$current])) {
                continue;
            }

            $visitedPages[$current] = true;
            $pageCount++;

            $pageResponse = $this->fetch($source, $current);
            $links = $this->extractArticleLinks($source, $pageResponse['body'], $current, $rules);

            foreach ($links as $link) {
                if (count($items) >= ($source->max_items > 0 ? $source->max_items : 50)) {
                    break 2;
                }

                if ($this->shouldSkipUrl($link, $rules, $source->source_url)) {
                    continue;
                }

                $articleResponse = $this->fetch($source, $link);

                $items[] = $this->extractArticleItem(
                    source: $source,
                    sourceUrl: $link,
                    html: $articleResponse['body'],
                    httpStatus: $articleResponse['status'],
                    fallbackTitle: null,
                    fallbackDescription: null,
                    fallbackImage: null,
                    fallbackAuthor: null,
                    fallbackPublishedAt: null,
                    rawPayload: [
                        'listing_page' => $current,
                        'link' => $link,
                    ],
                    fingerprintSeed: $link,
                    rules: $rules
                );
            }

            $nextPage = $this->extractNextPageUrl($pageResponse['body'], $current, $rules);

            if ($nextPage && ! isset($visitedPages[$nextPage]) && count($visitedPages) < $maxDepth + 1) {
                $queue[] = $nextPage;
            }
        }

        return $items;
    }

    protected function extractSitemapUrls(ContentSource $source, string $sitemapUrl, array &$visitedSitemaps, array $rules): array
    {
        if (isset($visitedSitemaps[$sitemapUrl])) {
            return [];
        }

        $visitedSitemaps[$sitemapUrl] = true;
        $response = $this->fetch($source, $sitemapUrl);
        $xml = @simplexml_load_string($response['body'], SimpleXMLElement::class, LIBXML_NOCDATA);

        if (! $xml) {
            return [];
        }

        $urls = [];

        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemap) {
                $child = trim((string) ($sitemap->loc ?? ''));
                if ($child !== '') {
                    $urls = array_merge($urls, $this->extractSitemapUrls($source, $child, $visitedSitemaps, $rules));
                }
            }

            return array_values(array_unique($urls));
        }

        if (isset($xml->url)) {
            foreach ($xml->url as $entry) {
                $loc = trim((string) ($entry->loc ?? ''));
                if ($loc === '') {
                    continue;
                }

                if ($this->shouldSkipUrl($loc, $rules, $source->source_url)) {
                    continue;
                }

                $urls[] = $loc;
            }
        }

        return array_values(array_unique($urls));
    }

    protected function extractArticleLinks(ContentSource $source, string $html, string $baseUrl, array $rules): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $links = [];

        $selector = $rules['article_link_xpath'] ?? $source->link_xpath ?? null;
        $expressions = $selector ? [$selector] : [
            '//article//a[@href]/@href',
            '//main//a[@href]/@href',
            '//h1//a[@href]/@href',
            '//h2//a[@href]/@href',
            '//h3//a[@href]/@href',
            '//a[@href]/@href',
        ];

        foreach ($expressions as $expression) {
            $nodes = $xpath->query($expression);
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                $href = trim((string) $node->nodeValue);
                $resolved = $this->resolveUrl($baseUrl, $href);

                if ($resolved !== '') {
                    $links[$resolved] = $resolved;
                }
            }

            if (! empty($links) && $selector) {
                break;
            }
        }

        return array_values(array_filter($links, fn ($url) => ! $this->shouldSkipUrl($url, $rules, $source->source_url)));
    }

    protected function extractNextPageUrl(string $html, string $baseUrl, array $rules): ?string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $expressions = [];

        if (! empty($rules['next_page_xpath'])) {
            $expressions[] = $rules['next_page_xpath'];
        }

        $expressions = array_merge($expressions, [
            '//a[@rel="next"]/@href',
            '//link[@rel="next"]/@href',
            '//a[contains(translate(normalize-space(.), "NEXT", "next"), "next")]/@href',
            '//a[contains(translate(normalize-space(.), "OLDER", "older"), "older")]/@href',
        ]);

        foreach ($expressions as $expression) {
            $nodes = $xpath->query($expression);
            if ($nodes && $nodes->length > 0) {
                $href = trim((string) $nodes->item(0)->nodeValue);
                $resolved = $this->resolveUrl($baseUrl, $href);

                if ($resolved !== '') {
                    return $resolved;
                }
            }
        }

        return null;
    }

    protected function extractArticleItem(
        ContentSource $source,
        string $sourceUrl,
        string $html,
        int $httpStatus,
        ?string $fallbackTitle,
        ?string $fallbackDescription,
        ?string $fallbackImage,
        ?string $fallbackAuthor,
        ?string $fallbackPublishedAt,
        array $rawPayload,
        string $fingerprintSeed,
        array $rules = []
    ): array {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $title = $this->metaContent($xpath, [
            $rules['title_xpath'] ?? null,
            $source->title_xpath,
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//title',
        ]);

        $description = $this->metaContent($xpath, [
            $rules['excerpt_xpath'] ?? null,
            $source->excerpt_xpath,
            '//meta[@property="og:description"]/@content',
            '//meta[@name="description"]/@content',
            '//meta[@name="twitter:description"]/@content',
        ]);

        $image = $this->metaContent($xpath, [
            $rules['image_xpath'] ?? null,
            $source->image_xpath,
            '//meta[@property="og:image"]/@content',
            '//meta[@name="twitter:image"]/@content',
            '//meta[@property="og:image:secure_url"]/@content',
        ]);

        $canonical = $this->metaContent($xpath, [
            '//link[@rel="canonical"]/@href',
        ]);

        $author = $this->metaContent($xpath, [
            $rules['author_xpath'] ?? null,
            '//meta[@name="author"]/@content',
            '//meta[@property="article:author"]/@content',
        ]);

        $publishedAt = $this->metaContent($xpath, [
            $rules['published_at_xpath'] ?? null,
            '//meta[@property="article:published_time"]/@content',
            '//meta[@property="og:article:published_time"]/@content',
            '//meta[@name="pubdate"]/@content',
        ]);

        $content = $this->extractMainContent($xpath, $source, $rules);
        if ($content === '') {
            $content = $this->bodyText($xpath);
        }

        $finalTitle = $this->cleanText($title ?: $fallbackTitle) ?: $source->name;
        $finalDescription = $this->cleanText($description ?: $fallbackDescription);
        $finalImage = $this->resolveUrl($source->source_url, $image ?: $fallbackImage ?: '');
        $finalCanonical = $canonical ? $this->resolveUrl($source->source_url, $canonical) : $sourceUrl;

        if ($finalDescription === '') {
            $finalDescription = Str::limit($this->cleanText($content), 220);
        }

        return [
            'fingerprint' => sha1($source->id.'|'.($finalCanonical ?: $fingerprintSeed)),
            'title' => $finalTitle,
            'meta_title' => $this->cleanText($title),
            'meta_description' => $this->cleanText($description),
            'canonical_url' => $finalCanonical,
            'url' => $sourceUrl,
            'excerpt' => $finalDescription,
            'content' => $content,
            'image_url' => $finalImage ?: null,
            'author' => $this->cleanText($author ?: $fallbackAuthor),
            'published_at' => $this->normalizeDate($publishedAt ?: $fallbackPublishedAt),
            'http_status' => $httpStatus,
            'raw_payload' => $rawPayload + [
                'canonical' => $canonical,
                'meta' => [
                    'title' => $title,
                    'description' => $description,
                    'image' => $image,
                    'author' => $author,
                    'published_at' => $publishedAt,
                ],
            ],
        ];
    }

    protected function extractMainContent(DOMXPath $xpath, ContentSource $source, array $rules): string
    {
        $expressions = [];

        if (! empty($rules['content_xpath'])) {
            $expressions[] = $rules['content_xpath'];
        }

        if ($source->content_xpath) {
            $expressions[] = $source->content_xpath;
        }

        $expressions = array_merge($expressions, [
            '//article//p',
            '//main//p',
            '//article',
            '//main',
            '//body//p',
        ]);

        $parts = [];
        foreach ($expressions as $expression) {
            if (! $expression) {
                continue;
            }

            $nodes = $xpath->query($expression);
            if (! $nodes || $nodes->length === 0) {
                continue;
            }

            foreach ($nodes as $node) {
                $text = $this->cleanText($node->textContent);
                if ($text !== '' && mb_strlen($text) > 20) {
                    $parts[] = $text;
                }
            }

            if (! empty($parts)) {
                break;
            }
        }

        return implode("\n\n", array_values(array_unique($parts)));
    }

    protected function bodyText(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//body//*[self::p or self::li or self::h2 or self::h3]');
        if (! $nodes || $nodes->length === 0) {
            return '';
        }

        $parts = [];
        foreach ($nodes as $node) {
            $text = $this->cleanText($node->textContent);
            if ($text !== '' && mb_strlen($text) > 20) {
                $parts[] = $text;
            }
        }

        return implode("\n\n", array_values(array_unique($parts)));
    }

    protected function metaContent(DOMXPath $xpath, array $expressions): string
    {
        foreach ($expressions as $expression) {
            if (! $expression) {
                continue;
            }

            $nodes = $xpath->query($expression);
            if ($nodes && $nodes->length > 0) {
                $value = trim((string) $nodes->item(0)->nodeValue);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    protected function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    protected function shouldSkipUrl(string $url, array $rules, string $baseUrl): bool
    {
        if ($url === '' || $url === $baseUrl) {
            return true;
        }

        $baseHost = parse_url($baseUrl, PHP_URL_HOST);
        $linkHost = parse_url($url, PHP_URL_HOST);

        if ($baseHost && $linkHost && $baseHost !== $linkHost) {
            return true;
        }

        foreach (($rules['deny_url_patterns'] ?? []) as $pattern) {
            if (@preg_match($pattern, $url) === 1) {
                return true;
            }
        }

        $allowed = $rules['allowed_url_patterns'] ?? [];
        if (! empty($allowed)) {
            foreach ($allowed as $pattern) {
                if (@preg_match($pattern, $url) === 1) {
                    return false;
                }
            }

            return true;
        }

        return Str::contains($url, ['mailto:', 'javascript:', '#', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.pdf']);
    }

    protected function extractFeedLink(SimpleXMLElement $feedItem): string
    {
        if (isset($feedItem->link)) {
            if ($feedItem->link instanceof SimpleXMLElement && $feedItem->link->attributes()) {
                $attributes = $feedItem->link->attributes();
                return (string) ($attributes['href'] ?? (string) $feedItem->link);
            }

            return (string) $feedItem->link;
        }

        return '';
    }

    protected function extractFeedImage(SimpleXMLElement $feedItem): string
    {
        if (isset($feedItem->enclosure)) {
            $attributes = $feedItem->enclosure->attributes();

            return (string) ($attributes['url'] ?? '');
        }

        return '';
    }

    protected function extractFeedAuthor(SimpleXMLElement $feedItem): string
    {
        return trim((string) ($feedItem->author ?? $feedItem->creator ?? ''));
    }

    protected function extractFeedDate(SimpleXMLElement $feedItem): ?string
    {
        $value = trim((string) ($feedItem->pubDate ?? $feedItem->published ?? $feedItem->updated ?? ''));

        return $this->normalizeDate($value);
    }

    protected function cleanText(?string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    protected function looksLikeFeed(string $body): bool
    {
        return str_contains($body, '<rss') || str_contains($body, '<feed') || str_contains($body, '<?xml');
    }

    protected function resolveUrl(string $baseUrl, string $link): string
    {
        $link = trim((string) $link);

        if ($link === '') {
            return '';
        }

        if (Str::startsWith($link, ['http://', 'https://', 'data:'])) {
            return $link;
        }

        $base = parse_url($baseUrl);

        if (! $base || empty($base['host'])) {
            return $link;
        }

        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'];
        $port = isset($base['port']) ? ':'.$base['port'] : '';

        if (Str::startsWith($link, '/')) {
            return "{$scheme}://{$host}{$port}{$link}";
        }

        $path = rtrim(dirname($base['path'] ?? '/'), '/\\');
        $path = $path === '.' ? '' : $path;

        return "{$scheme}://{$host}{$port}{$path}/{$link}";
    }

    protected function resolveRules(ContentSource $source): array
    {
        $rules = $source->rules_json ?? [];

        if (! is_array($rules)) {
            $rules = [];
        }

        return array_filter([
            'crawl_mode' => $rules['crawl_mode'] ?? $source->crawl_mode ?? null,
            'sitemap_url' => $rules['sitemap_url'] ?? $source->sitemap_url ?? null,
            'article_link_xpath' => $rules['article_link_xpath'] ?? null,
            'next_page_xpath' => $rules['next_page_xpath'] ?? $source->next_page_xpath ?? null,
            'title_xpath' => $rules['title_xpath'] ?? null,
            'content_xpath' => $rules['content_xpath'] ?? null,
            'excerpt_xpath' => $rules['excerpt_xpath'] ?? null,
            'image_xpath' => $rules['image_xpath'] ?? null,
            'author_xpath' => $rules['author_xpath'] ?? null,
            'published_at_xpath' => $rules['published_at_xpath'] ?? null,
            'allowed_url_patterns' => $rules['allowed_url_patterns'] ?? [],
            'deny_url_patterns' => $rules['deny_url_patterns'] ?? [],
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function peekBody(string $url, ContentSource $source): array
    {
        return $this->fetch($source, $url);
    }
}
