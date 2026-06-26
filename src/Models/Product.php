<?php

declare(strict_types=1);

namespace HeritageEdit\Models;

use HeritageEdit\Core\Database;
use Ramsey\Uuid\Uuid;

final class Product
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findBySlug(string $slug): ?array
    {
        $product = $this->db->fetch(
            'SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN brands b    ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.status = "active"',
            [$slug]
        );

        if (!$product) return null;

        $product['images']    = $this->getImages($product['id']);
        $product['variants']  = $this->getVariants($product['id']);
        $product['enrichment']= $this->getEnrichment($product['id']);
        $product['tags']      = $this->getTags($product['id']);

        return $product;
    }

    public function findById(string $id): ?array
    {
        return $this->db->fetch(
            'SELECT p.*, b.name AS brand_name, c.name AS category_name
             FROM products p
             LEFT JOIN brands b    ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public function catalog(array $filters = [], int $page = 1, int $perPage = 24): array
    {
        $where  = ['p.status = "active"'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]  = '(c.slug = ? OR parent_cat.slug = ?)';
            $params[] = $filters['category'];
            $params[] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $where[]  = 'b.slug = ?';
            $params[] = $filters['brand'];
        }
        if (!empty($filters['gender'])) {
            $where[]  = 'p.gender = ?';
            $params[] = $filters['gender'];
        }
        if (!empty($filters['min_price'])) {
            $where[]  = 'COALESCE(p.sale_price, p.base_price) >= ?';
            $params[] = (float) $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[]  = 'COALESCE(p.sale_price, p.base_price) <= ?';
            $params[] = (float) $filters['max_price'];
        }
        if (!empty($filters['color'])) {
            $where[]  = 'EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.color = ?)';
            $params[] = $filters['color'];
        }
        if (!empty($filters['size'])) {
            $where[]  = 'EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.size = ? AND v.stock > 0)';
            $params[] = $filters['size'];
        }
        if (!empty($filters['new_arrivals'])) {
            $where[] = 'p.is_new_arrival = 1';
        }

        $sort = match ($filters['sort'] ?? 'newest') {
            'price_asc'  => 'COALESCE(p.sale_price, p.base_price) ASC',
            'price_desc' => 'COALESCE(p.sale_price, p.base_price) DESC',
            'popular'    => 'p.total_sold DESC',
            default      => 'p.created_at DESC',
        };

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int) $this->db->fetch(
            "SELECT COUNT(DISTINCT p.id) AS cnt
             FROM products p
             LEFT JOIN brands b     ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN categories parent_cat ON parent_cat.id = c.parent_id
             WHERE $whereStr",
            $params
        )['cnt'];

        $products = $this->db->fetchAll(
            "SELECT p.id, p.slug, p.title, p.base_price, p.sale_price, p.currency,
                    p.is_new_arrival, p.is_featured,
                    b.name AS brand_name, b.slug AS brand_slug,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order LIMIT 1 OFFSET 1) AS hover_image
             FROM products p
             LEFT JOIN brands b     ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN categories parent_cat ON parent_cat.id = c.parent_id
             WHERE $whereStr
             GROUP BY p.id
             ORDER BY $sort
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'products'    => $products,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function featured(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.slug, p.title, p.base_price, p.sale_price, p.currency,
                    b.name AS brand_name,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order LIMIT 1 OFFSET 1) AS hover_image
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status = 'active' AND p.is_featured = 1
             ORDER BY p.total_sold DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function newArrivals(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.slug, p.title, p.base_price, p.sale_price, p.currency,
                    b.name AS brand_name,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS primary_image
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status = 'active' AND p.is_new_arrival = 1
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function create(array $data): string
    {
        $id = Uuid::uuid4()->toString();
        $slug = $this->generateSlug($data['title']);

        $this->db->insert('products', [
            'id'           => $id,
            'sku'          => $data['sku']         ?? $this->generateSku(),
            'slug'         => $slug,
            'title'        => $data['title'],
            'brand_id'     => $data['brand_id']    ?? null,
            'category_id'  => $data['category_id'] ?? null,
            'gender'       => $data['gender']       ?? 'women',
            'base_price'   => $data['base_price'],
            'sale_price'   => $data['sale_price']   ?? null,
            'currency'     => $data['currency']     ?? 'NGN',
            'status'       => $data['status']       ?? 'draft',
            'weight_grams' => $data['weight_grams'] ?? null,
            'ai_queued_at' => date('Y-m-d H:i:s'),
        ]);

        // Queue AI enrichment
        $this->db->insert('ai_job_queue', ['product_id' => $id, 'status' => 'pending']);

        return $id;
    }

    public function incrementViews(string $id): void
    {
        $this->db->query('UPDATE products SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    private function getImages(string $productId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order',
            [$productId]
        );
    }

    private function getVariants(string $productId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY size, color',
            [$productId]
        );
    }

    private function getEnrichment(string $productId): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM product_enrichments WHERE product_id = ?',
            [$productId]
        );
    }

    private function getTags(string $productId): array
    {
        return array_column(
            $this->db->fetchAll('SELECT tag FROM product_tags WHERE product_id = ?', [$productId]),
            'tag'
        );
    }

    private function generateSlug(string $title): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($base, '-');
        $i = 1;
        $candidate = $slug;
        while ($this->db->fetch('SELECT id FROM products WHERE slug = ?', [$candidate])) {
            $candidate = "$slug-$i";
            $i++;
        }
        return $candidate;
    }

    private function generateSku(): string
    {
        return 'HE-' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));
    }

    public function getFilterOptions(array $baseFilters = []): array
    {
        return [
            'brands' => $this->db->fetchAll(
                "SELECT b.id, b.name, b.slug, COUNT(p.id) AS product_count
                 FROM brands b JOIN products p ON p.brand_id = b.id
                 WHERE p.status = 'active'
                 GROUP BY b.id ORDER BY b.name"
            ),
            'sizes' => $this->db->fetchAll(
                "SELECT DISTINCT v.size, COUNT(DISTINCT v.product_id) AS product_count
                 FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE p.status = 'active' AND v.stock > 0 AND v.size IS NOT NULL
                 GROUP BY v.size ORDER BY v.size"
            ),
            'colors' => $this->db->fetchAll(
                "SELECT DISTINCT v.color, v.color_hex, COUNT(DISTINCT v.product_id) AS product_count
                 FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE p.status = 'active' AND v.color IS NOT NULL
                 GROUP BY v.color, v.color_hex ORDER BY v.color"
            ),
            'price_range' => $this->db->fetch(
                "SELECT MIN(COALESCE(sale_price, base_price)) AS min_price,
                        MAX(COALESCE(sale_price, base_price)) AS max_price
                 FROM products WHERE status = 'active'"
            ),
        ];
    }
}
