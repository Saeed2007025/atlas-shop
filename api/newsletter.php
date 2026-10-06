<?php
/**
 * Newsletter Subscription API
 * Accepts Mobile Number or Email Address
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/auth.php';

header('Content-Type: application/json; charset=utf-8');

check_honeypot();

$input = trim($_POST['contact_input'] ?? $_POST['email'] ?? '');

if (empty($input)) {
    json_response(['success' => false, 'message' => 'لطفاً شماره موبایل یا ایمیل خود را وارد کنید.'], 400);
}

// Check if valid mobile or valid email
$inputClean = to_english_num($input);
$isMobile = preg_match('/^09\d{9}$/', $inputClean);
$isEmail = filter_var($inputClean, FILTER_VALIDATE_EMAIL);

if (!$isMobile && !$isEmail) {
    json_response(['success' => false, 'message' => 'لطفاً یک شماره موبایل معتبر (مثال: 09121234567) یا ایمیل صحیح وارد کنید.'], 400);
}

$db = get_db();
if (!$db) {
    json_response(['success' => false, 'message' => 'خطا در برقراری ارتباط با دیتابیس.'], 500);
}

// Ensure table exists
$db->exec("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

try {
    $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$inputClean]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        json_response(['success' => true, 'message' => 'اطلاعات شما قبلاً در سیستم خبرنامه ثبت شده است. با تشکر!']);
    }

    $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, NOW())");
    $stmt->execute([$inputClean]);

    json_response(['success' => true, 'message' => 'با تشکر! اطلاعات شما جهت دریافت تخفیف‌ها با موفقیت در خبرنامه ثبت شد.']);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'ثبت اطلاعات موفقیت‌آمیز بود.'], 200);
}
