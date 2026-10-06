<?php
/**
 * Hardened Image Upload API for Admin
 * Validates extension, MIME-type, file header & randomizes filenames
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin_logged_in()) {
    json_response(['success' => false, 'message' => 'دسترسی غیرمجاز.'], 403);
}

if (empty($_FILES['image'])) {
    json_response(['success' => false, 'message' => 'فایلی ارسال نشده است.'], 400);
}

$file = $_FILES['image'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'message' => 'خطا هنگام آپلود فایل.'], 400);
}

if ($file['size'] > $maxSize) {
    json_response(['success' => false, 'message' => 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.'], 400);
}

// Strict extension whitelist
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

if (!in_array($ext, $allowedExts)) {
    json_response(['success' => false, 'message' => 'فرمت فایل مجاز نیست (فقط JPG, PNG, WebP).'], 400);
}

// Real Image Header Check via getimagesize
$imageInfo = @getimagesize($file['tmp_name']);
if (!$imageInfo) {
    json_response(['success' => false, 'message' => 'فایل آپلود شده یک تصویر معتبر نیست.'], 400);
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
if (!in_array($imageInfo['mime'], $allowedMimes)) {
    json_response(['success' => false, 'message' => 'نوع تصویر نامعتبر است.'], 400);
}

// Ensure uploads directory exists
if (!is_dir(PATH_UPLOADS)) {
    @mkdir(PATH_UPLOADS, 0755, true);
}

// Generate randomized filename
$newFilename = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$targetPath = PATH_UPLOADS . '/' . $newFilename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    json_response([
        'success' => true,
        'url' => 'uploads/' . $newFilename,
        'message' => 'تصویر با موفقیت آپلود شد.'
    ]);
} else {
    json_response(['success' => false, 'message' => 'ذخیره‌سازی تصویر ناموفق بود.'], 500);
}
