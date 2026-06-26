-- THE HERITAGE EDIT — MySQL 8.0+ Schema
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `heritage_edit`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `heritage_edit`;

-- ─── Users ───────────────────────────────────────────
CREATE TABLE `users` (
  `id`            CHAR(36)      NOT NULL PRIMARY KEY,
  `email`         VARCHAR(255)  NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)  NOT NULL,
  `first_name`    VARCHAR(100)  NOT NULL,
  `last_name`     VARCHAR(100)  NOT NULL,
  `phone`         VARCHAR(30)   DEFAULT NULL,
  `role`          ENUM('customer','admin','editor') NOT NULL DEFAULT 'customer',
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `email_verified_at` DATETIME  DEFAULT NULL,
  `last_login_at` DATETIME      DEFAULT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role`  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Addresses ───────────────────────────────────────
CREATE TABLE `addresses` (
  `id`          CHAR(36)     NOT NULL PRIMARY KEY,
  `user_id`     CHAR(36)     DEFAULT NULL,
  `type`        ENUM('shipping','billing') NOT NULL DEFAULT 'shipping',
  `first_name`  VARCHAR(100) NOT NULL,
  `last_name`   VARCHAR(100) NOT NULL,
  `company`     VARCHAR(200) DEFAULT NULL,
  `line1`       VARCHAR(255) NOT NULL,
  `line2`       VARCHAR(255) DEFAULT NULL,
  `city`        VARCHAR(100) NOT NULL,
  `state`       VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20)  NOT NULL,
  `country`     CHAR(2)      NOT NULL,
  `phone`       VARCHAR(30)  DEFAULT NULL,
  `is_default`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_addresses_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Categories ──────────────────────────────────────
CREATE TABLE `categories` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id`   INT UNSIGNED  DEFAULT NULL,
  `slug`        VARCHAR(100)  NOT NULL UNIQUE,
  `name`        VARCHAR(150)  NOT NULL,
  `description` TEXT          DEFAULT NULL,
  `image_url`   VARCHAR(500)  DEFAULT NULL,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Brands ──────────────────────────────────────────
CREATE TABLE `brands` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug`        VARCHAR(100)  NOT NULL UNIQUE,
  `name`        VARCHAR(150)  NOT NULL,
  `description` TEXT          DEFAULT NULL,
  `logo_url`    VARCHAR(500)  DEFAULT NULL,
  `country`     CHAR(2)       DEFAULT NULL,
  `founded_year` SMALLINT     DEFAULT NULL,
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Products ────────────────────────────────────────
CREATE TABLE `products` (
  `id`               CHAR(36)       NOT NULL PRIMARY KEY,
  `sku`              VARCHAR(80)    NOT NULL UNIQUE,
  `slug`             VARCHAR(250)   NOT NULL UNIQUE,
  `title`            VARCHAR(300)   NOT NULL,
  `brand_id`         INT UNSIGNED   DEFAULT NULL,
  `category_id`      INT UNSIGNED   DEFAULT NULL,
  `gender`           ENUM('women','men','unisex','kids') DEFAULT 'women',
  `base_price`       DECIMAL(12,2)  NOT NULL,
  `sale_price`       DECIMAL(12,2)  DEFAULT NULL,
  `currency`         CHAR(3)        NOT NULL DEFAULT 'NGN',
  `status`           ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
  `is_featured`      TINYINT(1)     NOT NULL DEFAULT 0,
  `is_new_arrival`   TINYINT(1)     NOT NULL DEFAULT 0,
  `weight_grams`     INT UNSIGNED   DEFAULT NULL,
  `ai_enriched`      TINYINT(1)     NOT NULL DEFAULT 0,
  `ai_queued_at`     DATETIME       DEFAULT NULL,
  `ai_enriched_at`   DATETIME       DEFAULT NULL,
  `total_sold`       INT UNSIGNED   NOT NULL DEFAULT 0,
  `view_count`       INT UNSIGNED   NOT NULL DEFAULT 0,
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`brand_id`)    REFERENCES `brands`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_products_slug`     (`slug`),
  INDEX `idx_products_brand`    (`brand_id`),
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_status`   (`status`),
  INDEX `idx_products_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Product AI Enrichment ───────────────────────────
CREATE TABLE `product_enrichments` (
  `product_id`            CHAR(36)   NOT NULL PRIMARY KEY,
  `history_and_heritage`  LONGTEXT   DEFAULT NULL,
  `when_to_wear`          TEXT       DEFAULT NULL,
  `right_occasion`        JSON       DEFAULT NULL,
  `style_recommendations` JSON       DEFAULT NULL,
  `material_story`        TEXT       DEFAULT NULL,
  `craftsmanship_notes`   TEXT       DEFAULT NULL,
  `raw_ai_response`       LONGTEXT   DEFAULT NULL,
  `created_at`            DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Product Images ───────────────────────────────────
CREATE TABLE `product_images` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id`  CHAR(36)      NOT NULL,
  `url`         VARCHAR(500)  NOT NULL,
  `alt_text`    VARCHAR(300)  DEFAULT NULL,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  `is_primary`  TINYINT(1)    NOT NULL DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_images_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Product Variants (Size × Color) ────────────────
CREATE TABLE `product_variants` (
  `id`          CHAR(36)       NOT NULL PRIMARY KEY,
  `product_id`  CHAR(36)       NOT NULL,
  `size`        VARCHAR(20)    DEFAULT NULL,
  `color`       VARCHAR(60)    DEFAULT NULL,
  `color_hex`   CHAR(7)        DEFAULT NULL,
  `sku_suffix`  VARCHAR(30)    DEFAULT NULL,
  `stock`       INT UNSIGNED   NOT NULL DEFAULT 0,
  `price_delta` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_variants_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Product Tags ─────────────────────────────────────
CREATE TABLE `product_tags` (
  `product_id`  CHAR(36)    NOT NULL,
  `tag`         VARCHAR(80) NOT NULL,
  PRIMARY KEY (`product_id`, `tag`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Sessions / Carts ────────────────────────────────
CREATE TABLE `carts` (
  `id`         CHAR(36)   NOT NULL PRIMARY KEY,
  `user_id`    CHAR(36)   DEFAULT NULL,
  `session_id` VARCHAR(128) DEFAULT NULL,
  `currency`   CHAR(3)    NOT NULL DEFAULT 'NGN',
  `expires_at` DATETIME   NOT NULL,
  `created_at` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_carts_session` (`session_id`),
  INDEX `idx_carts_user`    (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart_items` (
  `id`          CHAR(36)      NOT NULL PRIMARY KEY,
  `cart_id`     CHAR(36)      NOT NULL,
  `product_id`  CHAR(36)      NOT NULL,
  `variant_id`  CHAR(36)      DEFAULT NULL,
  `quantity`    SMALLINT      NOT NULL DEFAULT 1,
  `unit_price`  DECIMAL(12,2) NOT NULL,
  `added_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cart_id`)    REFERENCES `carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  INDEX `idx_cart_items_cart` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Orders ──────────────────────────────────────────
CREATE TABLE `orders` (
  `id`                  CHAR(36)        NOT NULL PRIMARY KEY,
  `order_number`        VARCHAR(30)     NOT NULL UNIQUE,
  `user_id`             CHAR(36)        DEFAULT NULL,
  `guest_email`         VARCHAR(255)    DEFAULT NULL,
  `status`              ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded')
                          NOT NULL DEFAULT 'pending',
  `payment_status`      ENUM('unpaid','paid','partially_refunded','refunded')
                          NOT NULL DEFAULT 'unpaid',
  `shipping_address_id` CHAR(36)        DEFAULT NULL,
  `billing_address_id`  CHAR(36)        DEFAULT NULL,
  `subtotal`            DECIMAL(14,2)   NOT NULL,
  `discount_amount`     DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `shipping_cost`       DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `duties_taxes`        DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `total`               DECIMAL(14,2)   NOT NULL,
  `currency`            CHAR(3)         NOT NULL DEFAULT 'NGN',
  `notes`               TEXT            DEFAULT NULL,
  `shipping_carrier`    VARCHAR(50)     DEFAULT NULL,
  `tracking_number`     VARCHAR(150)    DEFAULT NULL,
  `shipped_at`          DATETIME        DEFAULT NULL,
  `delivered_at`        DATETIME        DEFAULT NULL,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)             REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`billing_address_id`)  REFERENCES `addresses`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_user`   (`user_id`),
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id`            CHAR(36)      NOT NULL PRIMARY KEY,
  `order_id`      CHAR(36)      NOT NULL,
  `product_id`    CHAR(36)      DEFAULT NULL,
  `variant_id`    CHAR(36)      DEFAULT NULL,
  `product_title` VARCHAR(300)  NOT NULL,
  `variant_label` VARCHAR(100)  DEFAULT NULL,
  `sku`           VARCHAR(80)   DEFAULT NULL,
  `quantity`      SMALLINT      NOT NULL,
  `unit_price`    DECIMAL(12,2) NOT NULL,
  `total_price`   DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`)    REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Payments ────────────────────────────────────────
CREATE TABLE `payments` (
  `id`                  CHAR(36)       NOT NULL PRIMARY KEY,
  `order_id`            CHAR(36)       NOT NULL,
  `provider`            VARCHAR(30)    NOT NULL DEFAULT 'paystack',
  `provider_reference`  VARCHAR(200)   DEFAULT NULL,
  `provider_tx_id`      VARCHAR(200)   DEFAULT NULL,
  `amount`              DECIMAL(14,2)  NOT NULL,
  `currency`            CHAR(3)        NOT NULL,
  `status`              ENUM('initiated','pending','successful','failed','abandoned')
                          NOT NULL DEFAULT 'initiated',
  `payment_method`      VARCHAR(60)    DEFAULT NULL,
  `card_last4`          CHAR(4)        DEFAULT NULL,
  `metadata`            JSON           DEFAULT NULL,
  `paid_at`             DATETIME       DEFAULT NULL,
  `created_at`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_payments_order`     (`order_id`),
  INDEX `idx_payments_reference` (`provider_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Shipping Rates Cache ────────────────────────────
CREATE TABLE `shipping_rate_cache` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `origin_country`  CHAR(2)       NOT NULL,
  `dest_country`    CHAR(2)       NOT NULL,
  `carrier`         VARCHAR(30)   NOT NULL,
  `service_level`   VARCHAR(80)   NOT NULL,
  `weight_min_g`    INT UNSIGNED  NOT NULL,
  `weight_max_g`    INT UNSIGNED  NOT NULL,
  `rate_amount`     DECIMAL(10,2) NOT NULL,
  `currency`        CHAR(3)       NOT NULL DEFAULT 'USD',
  `estimated_days`  TINYINT       NOT NULL,
  `expires_at`      DATETIME      NOT NULL,
  INDEX `idx_shipping_dest` (`dest_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── AI Job Queue ─────────────────────────────────────
CREATE TABLE `ai_job_queue` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id`   CHAR(36)      NOT NULL,
  `status`       ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
  `attempts`     TINYINT       NOT NULL DEFAULT 0,
  `error`        TEXT          DEFAULT NULL,
  `scheduled_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at`   DATETIME      DEFAULT NULL,
  `finished_at`  DATETIME      DEFAULT NULL,
  INDEX `idx_ai_queue_status` (`status`),
  INDEX `idx_ai_queue_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Wishlist ─────────────────────────────────────────
CREATE TABLE `wishlists` (
  `user_id`    CHAR(36)  NOT NULL,
  `product_id` CHAR(36)  NOT NULL,
  `added_at`   DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Discount Codes ──────────────────────────────────
CREATE TABLE `discount_codes` (
  `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code`            VARCHAR(50)    NOT NULL UNIQUE,
  `type`            ENUM('percent','fixed') NOT NULL,
  `value`           DECIMAL(10,2)  NOT NULL,
  `min_order`       DECIMAL(12,2)  DEFAULT NULL,
  `max_uses`        INT UNSIGNED   DEFAULT NULL,
  `used_count`      INT UNSIGNED   NOT NULL DEFAULT 0,
  `valid_from`      DATETIME       DEFAULT NULL,
  `valid_until`     DATETIME       DEFAULT NULL,
  `is_active`       TINYINT(1)     NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ─── Seed: Categories ─────────────────────────────────
INSERT INTO `categories` (`id`,`parent_id`,`slug`,`name`,`sort_order`) VALUES
(1, NULL, 'women',         'Women',            1),
(2, NULL, 'men',           'Men',              2),
(3, 1,    'women-dresses', 'Dresses',          1),
(4, 1,    'women-tops',    'Tops & Blouses',   2),
(5, 1,    'women-coats',   'Coats & Jackets',  3),
(6, 1,    'women-shoes',   'Shoes',            4),
(7, 1,    'women-bags',    'Handbags',         5),
(8, 2,    'men-suits',     'Suits',            1),
(9, 2,    'men-outerwear', 'Outerwear',        2),
(10,2,    'men-shoes',     'Shoes',            3);
