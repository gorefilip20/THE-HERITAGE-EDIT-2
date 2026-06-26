# THE HERITAGE EDIT — Setup Guide

## Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js (optional, for Tailwind CLI build)
- A Paystack account (live or test)
- An EasyPost account (for live shipping rates)
- Anthropic API key (Claude)

---

## 1. Clone & Install

```bash
git clone <repo-url> heritage-edit
cd heritage-edit
composer install
cp .env.example .env
```

## 2. Configure `.env`

Fill in every value in `.env`:
- `APP_KEY` — generate with: `php -r "echo base64_encode(random_bytes(32));"`
- `DB_*` — your MySQL credentials
- `PAYSTACK_*` — from your Paystack dashboard
- `EASYPOST_API_KEY` — from EasyPost
- `ANTHROPIC_API_KEY` — from Anthropic Console

## 3. Database Setup

```bash
mysql -u root -p < database/schema.sql
```

## 4. Web Server

**Development (built-in PHP server):**
```bash
php -S localhost:8000 -t public/
```

**Production:** Use the provided `nginx.conf`. Point the document root to `public/`.

## 5. AI Enrichment Worker

Run as a cron job (every minute):
```
* * * * * /usr/bin/php /var/www/heritage-edit/src/Workers/ProductEnrichmentWorker.php >> /var/log/heritage_ai.log 2>&1
```

Or run manually:
```bash
composer run worker
```

## 6. Directory Permissions

```bash
chmod -R 755 public/assets/images/
```

---

## Architecture Overview

```
heritage-edit/
├── public/             # Web root (index.php + assets)
├── src/
│   ├── Core/           # Router, Database, Request, Response, Session
│   ├── Controllers/    # Product, Cart, Checkout, Admin
│   ├── Models/         # Product, Cart, Order
│   ├── Services/       # Paystack, Shipping (EasyPost), AI Enrichment
│   └── Workers/        # ProductEnrichmentWorker (CLI)
├── templates/
│   ├── layout/         # base.php, header.php, footer.php
│   ├── pages/          # home, catalog, product, checkout, confirmation
│   ├── admin/          # dashboard, products, orders, product-form
│   └── components/     # product-card
├── config/             # app, database, services
└── database/           # schema.sql
```

## Key Routes

| Method | Path                        | Description              |
|--------|-----------------------------|--------------------------|
| GET    | /                           | Home page                |
| GET    | /shop                       | Product catalog (PLP)    |
| GET    | /product/{slug}             | Product detail (PDP)     |
| GET    | /cart                       | Cart page                |
| POST   | /api/cart/add               | Add item to cart         |
| POST   | /api/cart/update            | Update cart item qty     |
| POST   | /api/cart/remove            | Remove cart item         |
| GET    | /checkout                   | Checkout page            |
| POST   | /api/shipping/rates         | Get live shipping rates  |
| POST   | /api/checkout/initialize    | Initialize Paystack tx   |
| GET    | /checkout/verify            | Verify payment callback  |
| POST   | /api/webhooks/paystack      | Paystack webhook         |
| GET    | /admin                      | Admin dashboard          |
| GET    | /admin/products/new         | Create product form      |
| POST   | /admin/products             | Store product            |

## The Heritage AI Engine

When a product is created via `/admin/products/new`:
1. A record is inserted into `ai_job_queue` with `status = 'pending'`
2. The worker (`ProductEnrichmentWorker.php`) picks it up on next cron run
3. It calls the Anthropic API with a structured luxury editorial prompt
4. The response is parsed and stored in `product_enrichments`
5. The PDP renders it in the "Heritage Narrative" tabbed component

## Payment Flow (Paystack)

1. Customer fills info → shipping → clicks "Pay Securely"
2. `POST /api/checkout/initialize` → creates Order (status: pending) → initializes Paystack
3. Paystack inline popup opens → customer pays
4. On success → `GET /checkout/verify?reference=xxx` → verifies with Paystack API
5. Order status updated to `confirmed`, payment_status to `paid`
6. Cart cleared → Order Confirmation page shown
7. Webhook at `/api/webhooks/paystack` provides server-side confirmation backup
