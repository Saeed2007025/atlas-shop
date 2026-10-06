<?php
/**
 * Contact Form API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

check_honeypot();

$name = trim($_POST['name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($mobile) || empty($message)) {
    json_response(['success' => false, 'message' => 'لطفاً نام، شماره تماس و متن پیام را وارد کنید.'], 400);
}

require_once __DIR__ . '/../inc/db.php';
$db = get_db();
$db->exec("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $db->prepare("INSERT INTO contact_messages (name, phone, subject, message) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $mobile, $subject, $message]);

json_response(['success' => true, 'message' => 'پیام شما با موفقیت دریافت شد. کارشناسان ما به زودی با شما تماس خواهند گرفت.']);
