# THE HERITAGE EDIT — Setup Guide

## Requirements
- PHP 8.1+ with extensions: `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`
- MySQL 8.0+
- **No Composer. No npm. Zero external dependencies.**

---

## 1. Clone & Configure

```bash
git clone https://github.com/gorefilip20/THE-HERITAGE-EDIT-2.git heritage-edit
cd heritage-edit
cp .env.example .env
# Fill in all values in .env
```

## 2. Database Setup

```bash
mysql -u root -p < database/schema.sql
```

## 3. Web Server

**Development (PHP built-in server):**
```bash
php -S localhost:8000 -t public/
```

**Production:** Configure Nginx using the provided `nginx.conf`.
Point document root to `public/`. All requests route through `public/index.php`.

## 4. File Permissions

```bash
mkdir -p public/assets/images/products
chmod -R 755 public/assets/images/
```

## 5. AI Enrichment Worker (cron)

```
* * * * * php /var/www/heritage-edit/src/Workers/ProductEnrichmentWorker.php >> /var/log/heritage_ai.log 2>&1
```

Or run manually:
```bash
php src/Workers/ProductEnrichmentWorker.php
```

---

## Architecture — Pure PHP 8, Zero Dependencies

```
heritage-edit/
├── public/
│   ├── index.php           # Entry point — bootstraps autoloader, env, router
│   ├── .htaccess           # Apache rewrite + security headers
│   └── assets/js/app.js   # Frontend cart/checkout/filter state machine
│
├── src/
│   ├── Core/
│   │   ├── Autoloader.php  # PSR-4 autoloader (replaces Composer)
│   │   ├── Env.php         # .env parser (replaces vlucas/phpdotenv)
│   │   ├── Uuid.php        # UUID v4 via random_bytes (replaces ramsey/uuid)
│   │   ├── HttpClient.php  # cURL HTTP client (replaces guzzlehttp/guzzle)
│   │   ├── Database.php    # PDO singleton
│   │   ├── Router.php      # Pattern-matching router
│   │   ├── Request.php     # HTTP request abstraction
│   │   ├── Response.php    # JSON + view renderer
│   │   └── Session.php     # Secure session wrapper
│   │
│   ├── Controllers/
│   │   ├── ProductController.php   # Home, catalog (PLP), PDP
│   │   ├── CartController.php      # Cart API endpoints
│   │   ├── CheckoutController.php  # 3-step checkout + Paystack + webhook
│   │   └── AdminController.php     # Dashboard, products, orders
│   │
│   ├── Models/
│   │   ├── Product.php     # Catalog queries, AI queue, enrichment
│   │   ├── Cart.php        # Session-aware cart state
│   │   └── Order.php       # Transactional order creation
│   │
│   ├── Services/
│   │   ├── PaystackService.php     # Payment gateway (cURL)
│   │   ├── ShippingService.php     # EasyPost rates + landed cost (cURL)
│   │   └── AIEnrichmentService.php # Anthropic Claude enrichment (cURL)
│   │
│   └── Workers/
│       └── ProductEnrichmentWorker.php  # CLI AI enrichment daemon
│
├── templates/
│   ├── layout/   base.php, header.php, footer.php
│   ├── pages/    home, catalog, product (PDP), checkout, order-confirmation
│   ├── admin/    dashboard, product-form, orders
│   └── components/ product-card.php
│
├── config/       app.php, database.php, services.php
├── database/     schema.sql  (14 tables, full MySQL 8 schema)
├── nginx.conf
└── .env.example
```

## Key Routes

| Method | Path                        | Handler                          |
|--------|-----------------------------|----------------------------------|
| GET    | /                           | Home (featured + new arrivals)   |
| GET    | /shop                       | Catalog with filter sidebar      |
| GET    | /product/{slug}             | PDP with Heritage Narrative      |
| GET    | /api/cart                   | Cart state (JSON)                |
| POST   | /api/cart/add               | Add item                         |
| POST   | /api/cart/update            | Update quantity                  |
| POST   | /api/cart/remove            | Remove item                      |
| GET    | /checkout                   | 3-step checkout page             |
| POST   | /api/shipping/rates         | EasyPost rates + landed cost     |
| POST   | /api/checkout/initialize    | Create order + Paystack init     |
| GET    | /checkout/verify            | Paystack callback verification   |
| POST   | /api/webhooks/paystack      | Paystack webhook (server-side)   |
| GET    | /admin                      | Admin dashboard                  |
| GET    | /admin/products/new         | Product upload form              |
| POST   | /admin/products             | Store product + queue AI         |

## PHP Extension Checklist

```bash
php -m | grep -E "pdo_mysql|curl|json|mbstring|openssl"
```

All five must appear. On Ubuntu/Debian:
```bash
sudo apt install php8.1-mysql php8.1-curl php8.1-mbstring
```
