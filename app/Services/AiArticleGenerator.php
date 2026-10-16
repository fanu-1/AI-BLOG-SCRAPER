<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiArticleGenerator
{
    public function generate(array $sourceData, AiProvider $provider, string $language = 'en'): array
    {
        $prompt = $this->buildPrompt($sourceData, $language);

        $responseText = match ($provider->driver) {
            'openai' => $this->generateWithOpenAi($provider, $prompt),
            'gemini' => $this->generateWithGemini($provider, $prompt),
            'claude' => $this->generateWithClaude($provider, $prompt),
            default => throw new \InvalidArgumentException("Unsupported AI driver: {$provider->driver}"),
        };

        $decoded = $this->decodeResponse($responseText);

        return [
            'title' => $decoded['title'] ?? ($sourceData['title'] ?? 'AI Generated Article'),
            'slug' => $decoded['slug'] ?? Str::slug($decoded['title'] ?? ($sourceData['title'] ?? 'article')),
            'excerpt' => $decoded['excerpt'] ?? ($sourceData['excerpt'] ?? null),
            'seo_title' => $decoded['seo_title'] ?? ($decoded['title'] ?? ($sourceData['title'] ?? null)),
            'seo_description' => $decoded['seo_description'] ?? ($decoded['excerpt'] ?? ($sourceData['excerpt'] ?? null)),
            'body_html' => $decoded['body_html'] ?? $this->fallbackBody($sourceData),
            'featured_image_url' => $decoded['featured_image_url'] ?? ($sourceData['image_url'] ?? null),
            'tags' => $decoded['tags'] ?? [],
            'raw' => $decoded,
            'raw_response' => $responseText,
        ];
    }

    protected function buildPrompt(array $sourceData, string $language): string
    {
        $sourceTitle = $sourceData['title'] ?? 'Untitled';
        $sourceExcerpt = $sourceData['excerpt'] ?? '';
        $sourceContent = $sourceData['content'] ?? '';
        $sourceUrl = $sourceData['url'] ?? '';

        return <<<PROMPT
You are a professional editorial writer.
Write a completely original article based on the scraped material below.
Use the same factual context, but create fresh structure, tone, examples, and perspective.
Return valid JSON only, with keys:
title, slug, excerpt, seo_title, seo_description, body_html, tags.

Rules:
- Write in {$language}.
- Keep the body in semantic HTML with headings, paragraphs, lists, and conclusion.
- Keep the article accurate, unique, and publication-ready.
- Avoid copying sentences from the source.

Source title: {$sourceTitle}
Source url: {$sourceUrl}
Source excerpt: {$sourceExcerpt}
Source content: {$sourceContent}
PROMPT;
    }

    protected function generateWithOpenAi(AiProvider $provider, string $prompt): string
    {
        $response = Http::withToken($provider->api_key)
            ->acceptJson()
            ->post(rtrim($provider->base_url ?: 'https://api.openai.com/v1', '/').'/chat/completions', [
                'model' => $provider->model,
                'temperature' => (float) $provider->temperature,
                'max_tokens' => $provider->max_tokens,
                'messages' => [
                    ['role' => 'system', 'content' => $provider->system_prompt ?: 'You write structured JSON articles.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        $response->throw();

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }

    protected function generateWithGemini(AiProvider $provider, string $prompt): string
    {
        $baseUrl = rtrim($provider->base_url ?: 'https://generativelanguage.googleapis.com/v1beta', '/');
        $response = Http::acceptJson()
            ->post($baseUrl.'/models/'.urlencode($provider->model).':generateContent?key='.urlencode((string) $provider->api_key), [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $provider->system_prompt ?: 'You write structured JSON articles.'],
                    ],
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => (float) $provider->temperature,
                    'maxOutputTokens' => $provider->max_tokens,
                ],
            ]);

        $response->throw();

        return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
    }

    protected function generateWithClaude(AiProvider $provider, string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $provider->api_key,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post(rtrim($provider->base_url ?: 'https://api.anthropic.com/v1', '/').'/messages', [
            'model' => $provider->model,
            'max_tokens' => $provider->max_tokens,
            'temperature' => (float) $provider->temperature,
            'system' => $provider->system_prompt ?: 'You write structured JSON articles.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        $response->throw();

        return (string) data_get($response->json(), 'content.0.text', '');
    }

    protected function decodeResponse(string $responseText): array
    {
        $trimmed = trim($responseText);
        $json = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        if (preg_match('/\{.*\}/s', $trimmed, $matches)) {
            $json = json_decode($matches[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }
        }

        return [
            'title' => 'AI Generated Article',
            'excerpt' => Str::limit($trimmed, 220),
            'body_html' => '<p>'.e($trimmed).'</p>',
        ];
    }

    protected function fallbackBody(array $sourceData): string
    {
        $content = trim((string) ($sourceData['content'] ?? ''));

        return '<p>'.e($content).'</p>';
    }
}
