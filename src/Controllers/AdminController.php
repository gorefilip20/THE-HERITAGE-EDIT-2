<?php

declare(strict_types=1);

namespace HeritageEdit\Controllers;

use HeritageEdit\Core\Database;
use HeritageEdit\Core\Request;
use HeritageEdit\Core\Response;
use HeritageEdit\Core\Session;
use HeritageEdit\Models\Product;
use Ramsey\Uuid\Uuid;

final class AdminController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->requireAdmin();
    }

    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/admin/login');
        }
        $user = $this->db->fetch('SELECT role FROM users WHERE id = ?', [Session::userId()]);
        if (($user['role'] ?? '') !== 'admin') {
            Response::abort(403, 'Forbidden');
        }
    }

    public function dashboard(Request $request): void
    {
        $stats = [
            'total_orders'   => $this->db->fetch('SELECT COUNT(*) AS c FROM orders')['c'],
            'total_revenue'  => $this->db->fetch("SELECT COALESCE(SUM(total),0) AS r FROM orders WHERE payment_status='paid'")['r'],
            'pending_orders' => $this->db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")['c'],
            'total_products' => $this->db->fetch("SELECT COUNT(*) AS c FROM products WHERE status='active'")['c'],
            'ai_pending'     => $this->db->fetch("SELECT COUNT(*) AS c FROM ai_job_queue WHERE status='pending'")['c'],
        ];

        $recent_orders = $this->db->fetchAll(
            "SELECT o.order_number, o.total, o.currency, o.status, o.payment_status, o.created_at,
                    COALESCE(u.email, o.guest_email) AS email
             FROM orders o LEFT JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        Response::view('admin/dashboard', compact('stats', 'recent_orders'));
    }

    public function products(Request $request): void
    {
        $products = $this->db->fetchAll(
            "SELECT p.id, p.title, p.sku, p.base_price, p.currency, p.status, p.ai_enriched,
                    b.name AS brand, c.name AS category, p.created_at
             FROM products p
             LEFT JOIN brands b     ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC LIMIT 100"
        );
        Response::view('admin/products', compact('products'));
    }

    public function createProductForm(Request $request): void
    {
        $brands     = $this->db->fetchAll('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name');
        $categories = $this->db->fetchAll('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name');
        Response::view('admin/product-form', compact('brands', 'categories'));
    }

    public function storeProduct(Request $request): void
    {
        $data = $request->all();

        $productModel = new Product();
        $productId = $productModel->create([
            'title'        => trim($data['title'] ?? ''),
            'brand_id'     => $data['brand_id']     ? (int) $data['brand_id']    : null,
            'category_id'  => $data['category_id']  ? (int) $data['category_id'] : null,
            'gender'       => $data['gender']        ?? 'women',
            'base_price'   => (float) ($data['base_price'] ?? 0),
            'sale_price'   => !empty($data['sale_price']) ? (float) $data['sale_price'] : null,
            'currency'     => $data['currency']      ?? 'NGN',
            'status'       => $data['status']        ?? 'draft',
            'weight_grams' => !empty($data['weight_grams']) ? (int) $data['weight_grams'] : null,
        ]);

        // Handle image uploads
        if (!empty($_FILES['images']['tmp_name'])) {
            $this->storeImages($productId, $_FILES['images']);
        }

        // Handle variants
        if (!empty($data['variants'])) {
            foreach (json_decode($data['variants'], true) as $variant) {
                $this->db->insert('product_variants', [
                    'id'         => Uuid::uuid4()->toString(),
                    'product_id' => $productId,
                    'size'       => $variant['size']      ?? null,
                    'color'      => $variant['color']     ?? null,
                    'color_hex'  => $variant['color_hex'] ?? null,
                    'stock'      => (int) ($variant['stock'] ?? 0),
                ]);
            }
        }

        Session::flash('success', 'Product created. AI enrichment queued.');
        Response::redirect('/admin/products');
    }

    private function storeImages(string $productId, array $files): void
    {
        $uploadDir = __DIR__ . '/../../public/assets/images/products/' . $productId . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $names = (array) $files['name'];
        $tmps  = (array) $files['tmp_name'];

        foreach ($names as $i => $name) {
            if (empty($tmps[$i])) continue;
            $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) continue;

            $filename = Uuid::uuid4()->toString() . '.' . $ext;
            move_uploaded_file($tmps[$i], $uploadDir . $filename);

            $this->db->insert('product_images', [
                'product_id' => $productId,
                'url'        => '/assets/images/products/' . $productId . '/' . $filename,
                'sort_order' => $i,
                'is_primary' => (int) ($i === 0),
            ]);
        }
    }

    public function orders(Request $request): void
    {
        $orders = $this->db->fetchAll(
            "SELECT o.*, COALESCE(u.email, o.guest_email) AS customer_email
             FROM orders o LEFT JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC LIMIT 50"
        );
        Response::view('admin/orders', compact('orders'));
    }

    public function orderDetail(Request $request): void
    {
        $id    = $request->param('id');
        $order = $this->db->fetch(
            "SELECT o.*, a.first_name, a.last_name, a.line1, a.city, a.country,
                    COALESCE(u.email, o.guest_email) AS customer_email
             FROM orders o
             LEFT JOIN addresses a ON a.id = o.shipping_address_id
             LEFT JOIN users u     ON u.id = o.user_id
             WHERE o.id = ?",
            [$id]
        );
        if (!$order) Response::abort(404);

        $order['items'] = $this->db->fetchAll(
            'SELECT * FROM order_items WHERE order_id = ?', [$id]
        );
        Response::view('admin/order-detail', compact('order'));
    }
}
