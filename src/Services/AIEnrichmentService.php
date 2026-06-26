<?php

declare(strict_types=1);

namespace HeritageEdit\Services;

use GuzzleHttp\Client;
use HeritageEdit\Core\Database;

final class AIEnrichmentService
{
    private Client $http;
    private array $config;
    private Database $db;

    public function __construct()
    {
        $services    = require __DIR__ . '/../../config/services.php';
        $this->config = $services['anthropic'];
        $this->db     = Database::getInstance();
        $this->http   = new Client(['timeout' => 60]);
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

        $prompt = $this->buildPrompt($product);

        try {
            $response = $this->http->post($this->config['base_url'] . '/messages', [
                'headers' => [
                    'x-api-key'         => $this->config['api_key'],
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ],
                'json' => [
                    'model'      => $this->config['model'],
                    'max_tokens' => $this->config['max_tokens'],
                    'system'     => $this->systemPrompt(),
                    'messages'   => [['role' => 'user', 'content' => $prompt]],
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);
            $raw  = $body['content'][0]['text'] ?? '';
            $data = $this->parseResponse($raw);

            if (!$data) return false;

            // Upsert enrichment
            $existing = $this->db->fetch('SELECT product_id FROM product_enrichments WHERE product_id = ?', [$productId]);

            if ($existing) {
                $this->db->update('product_enrichments', [
                    'history_and_heritage'  => $data['history_and_heritage']  ?? null,
                    'when_to_wear'          => $data['when_to_wear']          ?? null,
                    'right_occasion'        => json_encode($data['right_occasion'] ?? []),
                    'style_recommendations' => json_encode($data['style_recommendations'] ?? []),
                    'material_story'        => $data['material_story']        ?? null,
                    'craftsmanship_notes'   => $data['craftsmanship_notes']   ?? null,
                    'raw_ai_response'       => $raw,
                ], 'product_id = ?', [$productId]);
            } else {
                $this->db->insert('product_enrichments', [
                    'product_id'            => $productId,
                    'history_and_heritage'  => $data['history_and_heritage']  ?? null,
                    'when_to_wear'          => $data['when_to_wear']          ?? null,
                    'right_occasion'        => json_encode($data['right_occasion'] ?? []),
                    'style_recommendations' => json_encode($data['style_recommendations'] ?? []),
                    'material_story'        => $data['material_story']        ?? null,
                    'craftsmanship_notes'   => $data['craftsmanship_notes']   ?? null,
                    'raw_ai_response'       => $raw,
                ]);
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
  "history_and_heritage": "3-4 sentences. The origin story, historical significance, or design DNA behind this silhouette, fabric, or archetype. Reference the house's founding philosophy, archival references, or textile geography where relevant.",
  "when_to_wear": "2-3 sentences. Micro-guidance on timing, seasonality, and styling dynamics. Describe the emotional register and dress code context.",
  "right_occasion": ["Black Tie Gala", "Mediterranean Riviera Resort", "Apres-Ski Lounge"],
  "style_recommendations": [
    {
      "item": "Item name",
      "category": "Category",
      "reason": "One sentence on why it completes the look"
    }
  ],
  "material_story": "2 sentences on the craftsmanship, fabric origin, or construction technique that elevates this piece.",
  "craftsmanship_notes": "1-2 sentences on the artisanal detail, finishing, or heritage technique that distinguishes it."
}

Return 3-4 occasion strings and 3 style recommendation objects.
PROMPT;
    }

    private function parseResponse(string $raw): ?array
    {
        // Strip any accidental markdown fences
        $raw = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $raw = preg_replace('/\s*```$/m', '', $raw);
        $data = json_decode(trim($raw), true);
        return is_array($data) ? $data : null;
    }
}
