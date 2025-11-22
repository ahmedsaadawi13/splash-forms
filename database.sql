-- FILE: /database.sql
-- SplashForms Multi-Tenant Form Builder Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.2+

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ====================================================================
-- TENANTS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `domain` VARCHAR(255) UNIQUE DEFAULT NULL,
  `status` ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
  `settings` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_domain` (`domain`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- PLANS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `billing_period` ENUM('monthly', 'yearly') DEFAULT 'monthly',
  `max_forms` INT DEFAULT 10,
  `max_submissions_per_month` INT DEFAULT 1000,
  `max_file_size_mb` INT DEFAULT 10,
  `features` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TENANT SUBSCRIPTIONS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `tenant_subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'trial',
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT,
  INDEX `idx_tenant_status` (`tenant_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- USERS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'staff', 'user') DEFAULT 'user',
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_email_tenant` (`email`, `tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  INDEX `idx_tenant_role` (`tenant_id`, `role`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- API KEYS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `api_key` VARCHAR(64) UNIQUE NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  INDEX `idx_api_key` (`api_key`),
  INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- FORMS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `forms` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `schema` LONGTEXT NOT NULL,
  `settings` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `submissions_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_slug_tenant` (`slug`, `tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_tenant_active` (`tenant_id`, `is_active`),
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- FORM SUBMISSIONS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `form_id` INT UNSIGNED NOT NULL,
  `data` LONGTEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('new', 'read', 'spam', 'archived') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`) ON DELETE CASCADE,
  INDEX `idx_form_status` (`form_id`, `status`),
  INDEX `idx_tenant_created` (`tenant_id`, `created_at`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- WEBHOOKS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `webhooks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `form_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `events` VARCHAR(255) DEFAULT 'submission.created',
  `secret` VARCHAR(64) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`) ON DELETE CASCADE,
  INDEX `idx_form_active` (`form_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- WEBHOOK LOGS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `webhook_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `webhook_id` INT UNSIGNED NOT NULL,
  `submission_id` INT UNSIGNED DEFAULT NULL,
  `payload` TEXT DEFAULT NULL,
  `response` TEXT DEFAULT NULL,
  `status_code` INT DEFAULT NULL,
  `success` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`webhook_id`) REFERENCES `webhooks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`submission_id`) REFERENCES `form_submissions`(`id`) ON DELETE SET NULL,
  INDEX `idx_webhook_created` (`webhook_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- FORM TEMPLATES TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `form_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'general',
  `schema` LONGTEXT NOT NULL,
  `preview_image` VARCHAR(500) DEFAULT NULL,
  `is_public` TINYINT(1) DEFAULT 1,
  `usage_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- INVOICES TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED DEFAULT NULL,
  `invoice_number` VARCHAR(100) UNIQUE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions`(`id`) ON DELETE SET NULL,
  INDEX `idx_tenant_status` (`tenant_id`, `status`),
  INDEX `idx_invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- PAYMENTS TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `method` VARCHAR(50) DEFAULT 'credit_card',
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `metadata` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
  INDEX `idx_tenant` (`tenant_id`),
  INDEX `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- USAGE TRACKING TABLE
-- ====================================================================
CREATE TABLE IF NOT EXISTS `usage_tracking` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `metric` VARCHAR(50) NOT NULL,
  `value` INT NOT NULL DEFAULT 0,
  `period` VARCHAR(20) NOT NULL,
  `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_tenant_metric_period` (`tenant_id`, `metric`, `period`),
  INDEX `idx_tenant_period` (`tenant_id`, `period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- SEED DATA
-- ====================================================================

-- Insert default plans
INSERT INTO `plans` (`name`, `slug`, `price`, `billing_period`, `max_forms`, `max_submissions_per_month`, `max_file_size_mb`, `features`) VALUES
('Free', 'free', 0.00, 'monthly', 3, 100, 5, '["basic_forms", "email_notifications"]'),
('Starter', 'starter', 19.00, 'monthly', 10, 1000, 10, '["basic_forms", "email_notifications", "webhooks", "csv_export"]'),
('Professional', 'professional', 49.00, 'monthly', 50, 10000, 50, '["basic_forms", "email_notifications", "webhooks", "csv_export", "custom_domains", "api_access"]'),
('Enterprise', 'enterprise', 199.00, 'monthly', 999, 100000, 100, '["basic_forms", "email_notifications", "webhooks", "csv_export", "custom_domains", "api_access", "priority_support", "white_label"]');

-- Insert demo tenant
INSERT INTO `tenants` (`name`, `domain`, `status`) VALUES
('Demo Company', 'demo.splashforms.local', 'active');

-- Insert platform admin
INSERT INTO `users` (`tenant_id`, `email`, `password`, `name`, `role`) VALUES
(NULL, 'admin@splashforms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform Admin', 'platform_admin');
-- Password: password

-- Insert demo tenant admin
INSERT INTO `users` (`tenant_id`, `email`, `password`, `name`, `role`) VALUES
(1, 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User', 'tenant_admin');
-- Password: password

-- Insert demo subscription
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `expires_at`) VALUES
(1, 2, 'active', DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Insert demo API key
INSERT INTO `api_keys` (`tenant_id`, `api_key`, `name`) VALUES
(1, 'demo_1234567890abcdef1234567890abcdef1234567890abcdef1234567890', 'Demo API Key');

-- Insert form templates
INSERT INTO `form_templates` (`name`, `slug`, `description`, `category`, `schema`) VALUES
('Contact Form', 'contact-form', 'Simple contact form with name, email, and message', 'general', '[{"type":"text","label":"Name","name":"name","required":true,"placeholder":"Enter your name"},{"type":"email","label":"Email","name":"email","required":true,"placeholder":"your@email.com"},{"type":"textarea","label":"Message","name":"message","required":true,"placeholder":"Your message here..."}]'),
('Newsletter Signup', 'newsletter-signup', 'Email subscription form', 'marketing', '[{"type":"email","label":"Email Address","name":"email","required":true,"placeholder":"your@email.com"},{"type":"checkbox","label":"I agree to receive emails","name":"consent","required":true}]'),
('Job Application', 'job-application', 'Complete job application form', 'hr', '[{"type":"text","label":"Full Name","name":"full_name","required":true},{"type":"email","label":"Email","name":"email","required":true},{"type":"tel","label":"Phone","name":"phone","required":true},{"type":"file","label":"Resume","name":"resume","required":true},{"type":"textarea","label":"Cover Letter","name":"cover_letter","required":false}]'),
('Event Registration', 'event-registration', 'Event registration form', 'events', '[{"type":"text","label":"Name","name":"name","required":true},{"type":"email","label":"Email","name":"email","required":true},{"type":"select","label":"Number of Attendees","name":"attendees","required":true,"options":["1","2","3","4","5+"]},{"type":"textarea","label":"Special Requirements","name":"requirements","required":false}]');

-- Insert demo form
INSERT INTO `forms` (`tenant_id`, `user_id`, `name`, `slug`, `description`, `schema`, `settings`, `submissions_count`) VALUES
(1, 2, 'Contact Us', 'contact-us', 'Get in touch with our team', '[{"type":"text","label":"Name","name":"name","required":true,"placeholder":"Enter your name"},{"type":"email","label":"Email","name":"email","required":true,"placeholder":"your@email.com"},{"type":"select","label":"Subject","name":"subject","required":true,"options":["General Inquiry","Support","Sales","Feedback"]},{"type":"textarea","label":"Message","name":"message","required":true,"placeholder":"Your message here..."}]', '{"redirect_url":"","success_message":"Thank you for contacting us!","notification_email":"demo@example.com","enable_spam_protection":true}', 5);

-- Insert demo submissions
INSERT INTO `form_submissions` (`tenant_id`, `form_id`, `data`, `ip_address`, `status`) VALUES
(1, 1, '{"name":"John Doe","email":"john@example.com","subject":"General Inquiry","message":"Hello, I would like to learn more about your platform."}', '192.168.1.100', 'new'),
(1, 1, '{"name":"Jane Smith","email":"jane@example.com","subject":"Support","message":"I need help with my account."}', '192.168.1.101', 'read'),
(1, 1, '{"name":"Bob Johnson","email":"bob@example.com","subject":"Sales","message":"I am interested in the Enterprise plan."}', '192.168.1.102', 'new'),
(1, 1, '{"name":"Alice Williams","email":"alice@example.com","subject":"Feedback","message":"Great platform! Easy to use."}', '192.168.1.103', 'read'),
(1, 1, '{"name":"Charlie Brown","email":"charlie@example.com","subject":"General Inquiry","message":"How can I integrate this with my website?"}', '192.168.1.104', 'new');

-- Update submissions count
UPDATE `forms` SET `submissions_count` = (SELECT COUNT(*) FROM `form_submissions` WHERE `form_id` = 1) WHERE `id` = 1;
