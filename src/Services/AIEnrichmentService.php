<?php

declare(strict_types=1);

namespace HeritageEdit\Services;

use HeritageEdit\Core\Database;
use HeritageEdit\Core\HttpClient;
use HeritageEdit\Core\Env;

final class AIEnrichmentService
{
    private HttpClient $http;
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private Database $db;

    public function __construct()
    {
        $this->apiKey  = Env::get('ANTHROPIC_API_KEY', '');
        $this->model   = Env::get('ANTHROPIC_MODEL', 'claude-sonnet-4-6');
        $this->baseUrl = 'https://api.anthropic.com/v1';
        $this->db      = Database::getInstance();
        $this->http    = new HttpClient([
            'timeout' => 60,
            'headers' => [
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ],
        ]);
    }

    public function enrich(string $productId): bool
    {
        $product = $this->db->fetch(
            'SELECT p.title, b.name AS brand, c.name AS category, p.gender
             FROM products p
             LEFT JOIN brands b     ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?',
            [$productId]
        );

        if (!$product) return false;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 2000,
            'system'     => $this->systemPrompt(),
            'messages'   => [['role' => 'user', 'content' => $this->buildPrompt($product)]],
        ];

        try {
            $response = $this->http->post($this->baseUrl . '/messages', $payload)->throw();
            $body     = $response->json();
            $raw      = $body['content'][0]['text'] ?? '';
            $data     = $this->parseResponse($raw);

            if (!$data) return false;

            $existing = $this->db->fetch(
                'SELECT product_id FROM product_enrichments WHERE product_id = ?',
                [$productId]
            );

            $row = [
                'history_and_heritage'  => $data['history_and_heritage']  ?? null,
                'when_to_wear'          => $data['when_to_wear']          ?? null,
                'right_occasion'        => json_encode($data['right_occasion']        ?? []),
                'style_recommendations' => json_encode($data['style_recommendations'] ?? []),
                'material_story'        => $data['material_story']        ?? null,
                'craftsmanship_notes'   => $data['craftsmanship_notes']   ?? null,
                'raw_ai_response'       => $raw,
            ];

            if ($existing) {
                $this->db->update('product_enrichments', $row, 'product_id = ?', [$productId]);
            } else {
                $this->db->insert('product_enrichments', array_merge(['product_id' => $productId], $row));
            }

            $this->db->update('products', [
                'ai_enriched'    => 1,
                'ai_enriched_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$productId]);

            return true;

        } catch (\Throwable $e) {
            error_log("[AIEnrichment] Failed for $productId: " . $e->getMessage());
            return false;
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are the Head Curator of THE HERITAGE EDIT, an ultra-luxury fashion platform.
Your role is to craft deeply researched, poetic, and authoritative product narratives
that rival Vogue editorial copy. You have encyclopedic knowledge of haute couture
houses, fashion history, textile provenance, and styling dynamics.

Respond ONLY with a valid JSON object — no markdown fences, no explanation outside the JSON.
PROMPT;
    }

    private function buildPrompt(array $product): string
    {
        $title    = $product['title'];
        $brand    = $product['brand']    ?? 'Unknown Brand';
        $category = $product['category'] ?? 'Fashion';
        $gender   = $product['gender']   ?? 'women';

        return <<<PROMPT
Product details:
- Title: {$title}
- Brand: {$brand}
- Category: {$category}
- Gender: {$gender}

Generate a luxury product narrative as a JSON object with these exact keys:

{
  "history_and_heritage": "3-4 sentences. The origin story, historical significance, or design DNA behind this silhouette, fabric, or archetype.",
  "when_to_wear": "2-3 sentences. Micro-guidance on timing, seasonality, and styling dynamics.",
  "right_occasion": ["Black Tie Gala", "Mediterranean Riviera Resort", "Apres-Ski Lounge"],
  "style_recommendations": [
    { "item": "Item name", "category": "Category", "reason": "One sentence on why it completes the look" }
  ],
  "material_story": "2 sentences on the craftsmanship, fabric origin, or construction technique.",
  "craftsmanship_notes": "1-2 sentences on the artisanal detail or finishing technique."
}

Return 3-4 occasion strings and 3 style recommendation objects.
PROMPT;
    }

    private function parseResponse(string $raw): ?array
    {
        $raw  = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw  = preg_replace('/\s*```$/m', '', $raw);
        $data = json_decode(trim($raw), true);
        return is_array($data) ? $data : null;
    }
}
