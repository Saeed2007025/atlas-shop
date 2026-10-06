-- ============================================================
-- Atlas Shop Database Schema (MySQL / MariaDB)
-- UTF8MB4 for full Persian language support
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `payment_transactions`;
DROP TABLE IF EXISTS `coupon_usages`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `customer_otps`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `newsletter_subscribers`;
DROP TABLE IF EXISTS `product_reviews`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `shipping_methods`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `banners`;

-- 1. Admins Table
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Settings Table
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Shipping Methods Table
CREATE TABLE `shipping_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `cost` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `free_threshold` DECIMAL(12, 0) DEFAULT 0,
  `allowed_provinces` VARCHAR(255) DEFAULT 'all',
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT DEFAULT 0,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `image` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Products Table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `brand` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `discount_price` DECIMAL(12, 0) DEFAULT NULL,
  `discount_percent` INT DEFAULT 0,
  `stock` INT DEFAULT 0,
  `rating` DECIMAL(2, 1) DEFAULT 5.0,
  `reviews_count` INT DEFAULT 0,
  `description` LONGTEXT DEFAULT NULL,
  `highlights` TEXT DEFAULT NULL,
  `specifications` LONGTEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_new` TINYINT(1) DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Product Reviews Table
CREATE TABLE `product_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_mobile` VARCHAR(20) DEFAULT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Product Images Table
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `display_order` INT DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Product Variants Table
CREATE TABLE `product_variants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'color',
  `value` VARCHAR(100) NOT NULL,
  `price_adjustment` DECIMAL(12, 0) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Customers Table
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(50) DEFAULT NULL,
  `city` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Newsletter Subscribers Table
CREATE TABLE `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Customer OTPs Table
CREATE TABLE `customer_otps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mobile` VARCHAR(20) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Orders Table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT DEFAULT NULL,
  `receiver_name` VARCHAR(100) NOT NULL,
  `receiver_mobile` VARCHAR(20) NOT NULL,
  `receiver_email` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(50) NOT NULL,
  `city` VARCHAR(50) NOT NULL,
  `district` VARCHAR(100) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `shipping_method` VARCHAR(50) DEFAULT 'post',
  `shipping_cost` DECIMAL(12, 0) DEFAULT 0,
  `subtotal` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12, 0) DEFAULT 0,
  `total_amount` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `payment_status` VARCHAR(20) DEFAULT 'pending',
  `order_status` VARCHAR(20) DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Order Items Table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(12, 0) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `total_price` DECIMAL(12, 0) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Coupons Table
CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
  `value` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `min_order_amount` DECIMAL(12, 0) DEFAULT 0,
  `max_discount` DECIMAL(12, 0) DEFAULT 0,
  `usage_limit` INT DEFAULT 100,
  `times_used` INT DEFAULT 0,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Coupon Usages Table
CREATE TABLE `coupon_usages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT NOT NULL,
  `customer_id` INT DEFAULT NULL,
  `order_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Blog Posts Table
CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT DEFAULT NULL,
  `body` LONGTEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `author` VARCHAR(100) DEFAULT 'تیم تحریریه اطلس شاپ',
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `views` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Banners Table
CREATE TABLE `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `position` ENUM('hero', 'promo') NOT NULL DEFAULT 'hero',
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Payment Transactions Table
CREATE TABLE `payment_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `authority` VARCHAR(255) NOT NULL,
  `ref_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(12, 0) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'pending',
  `gateway_response` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_type ENUM('admin','otp_send','otp_verify') NOT NULL,
    identifier VARCHAR(100),
    attempts INT DEFAULT 1,
    lockout_until DATETIME NULL,
    last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_type (ip_address, attempt_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
