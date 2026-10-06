<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/db.php';

// Safe output escape
function e($string) {
    if ($string === null) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Convert English numbers to Persian
function to_persian_num($number) {
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($en, $fa, (string)$number);
}

// Convert Persian/Arabic numbers to English
function to_english_num($number) {
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $number = str_replace($fa, $en, (string)$number);
    return str_replace($ar, $en, $number);
}

// Format price in Toman
function format_price($amount, $unit = 'تومان') {
    if ($amount === null || $amount === '') return '۰ ' . $unit;
    $num = number_format((float)$amount);
    return to_persian_num($num) . ' ' . $unit;
}

// CSRF Token Management
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        json_response(['success' => false, 'message' => 'اعتبار‌سنجی فرم ناموفق بود (CSRF). لطفاً صفحه را بازخوانی کنید.'], 403);
    }
    return true;
}

// Honeypot Anti-Spam
function honeypot_field() {
    return '<div style="display:none !important;" aria-hidden="true"><input type="text" name="hp_website_check" value="" tabindex="-1" autocomplete="off"></div>';
}

function check_honeypot() {
    if (!empty($_POST['hp_website_check'])) {
        json_response(['success' => false, 'message' => 'درخواست مشکوک شناسایی شد.'], 400);
    }
    return true;
}

// JSON API Response
function json_response($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Redirect Helper
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper to check AJAX request
function is_ajax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
           (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
}

// Slug generator for Persian & English
function slugify($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/u', '-', $text);
    return trim($text, '-');
}

// Get Setting from Database
function get_setting($key, $default = '') {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $settingsCache = [];
        $db = get_db();
        if ($db) {
            try {
                $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
                while ($row = $stmt->fetch()) {
                    $settingsCache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {}
        }
    }
    return $settingsCache[$key] ?? $default;
}

// Set Setting in Database (ANSI SQL compatible)
function set_setting($key, $value) {
    $db = get_db();
    if (!$db) return false;
    try {
        $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $settingId = $stmt->fetchColumn();

        if ($settingId) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE id = ?");
            return $stmt->execute([$value, $settingId]);
        } else {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            return $stmt->execute([$key, $value]);
        }
    } catch (Exception $e) {
        return false;
    }
}

// Flash Message System
function set_flash($type, $message) {
    $_SESSION['flash_messages'][$type][] = $message;
}

function get_flashes() {
    $flashes = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $flashes;
}

/**
 * Automatic Banner Resizing & Smart Center Crop Tool (GD Library)
 * Resizes and crops uploaded banners to exact aspect ratios in WebP format
 */
function process_and_save_banner_image($tmpFile, $targetPath, $targetWidth = 1200, $targetHeight = 480) {
    if (!file_exists($tmpFile) || !function_exists('imagecreatefromstring')) {
        return move_uploaded_file($tmpFile, $targetPath);
    }

    $imgData = @file_get_contents($tmpFile);
    if (!$imgData) {
        return move_uploaded_file($tmpFile, $targetPath);
    }

    $srcImg = @imagecreatefromstring($imgData);
    if (!$srcImg) {
        return move_uploaded_file($tmpFile, $targetPath);
    }

    $origWidth = imagesx($srcImg);
    $origHeight = imagesy($srcImg);

    if ($origWidth <= 0 || $origHeight <= 0) {
        imagedestroy($srcImg);
        return move_uploaded_file($tmpFile, $targetPath);
    }

    // Calculate smart center crop
    $targetRatio = $targetWidth / $targetHeight;
    $origRatio = $origWidth / $origHeight;

    if ($origRatio > $targetRatio) {
        // Image is wider: crop sides
        $cropWidth = (int)($origHeight * $targetRatio);
        $cropHeight = $origHeight;
        $srcX = (int)(($origWidth - $cropWidth) / 2);
        $srcY = 0;
    } else {
        // Image is taller: crop top and bottom
        $cropWidth = $origWidth;
        $cropHeight = (int)($origWidth / $targetRatio);
        $srcX = 0;
        $srcY = (int)(($origHeight - $cropHeight) / 2);
    }

    // Create target canvas
    $dstImg = imagecreatetruecolor($targetWidth, $targetHeight);

    // Preserve transparency for PNG/WebP
    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);

    // Resample with high quality
    imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

    // Save as WebP if supported, or JPEG
    $saved = false;
    if (function_exists('imagewebp')) {
        $saved = imagewebp($dstImg, $targetPath, 92);
    } elseif (function_exists('imagejpeg')) {
        $saved = imagejpeg($dstImg, $targetPath, 92);
    }

    imagedestroy($srcImg);
    imagedestroy($dstImg);

    if (!$saved) {
        return move_uploaded_file($tmpFile, $targetPath);
    }

    return true;
}

/**
 * Validate and sanitize uploaded file - prevents RCE via file upload
 * @param array $file - $_FILES['fieldname']
 * @param array $allowedExtensions - e.g. ['jpg','jpeg','png','webp','gif']
 * @param int $maxSize - max file size in bytes (default 5MB)
 * @return array ['valid' => bool, 'error' => string, 'ext' => string]
 */
function validate_upload($file, $allowedExtensions = ['jpg','jpeg','png','webp','gif','svg'], $maxSize = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'خطا در آپلود فایل', 'ext' => ''];
    }
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'حجم فایل بیش از حد مجاز است', 'ext' => ''];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', $allowedExtensions), 'ext' => ''];
    }
    // Verify MIME type matches extension
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'webp' => 'image/webp',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml'
    ];
    if (isset($allowedMimes[$ext]) && $mimeType !== $allowedMimes[$ext]) {
        return ['valid' => false, 'error' => 'محتوای فایل با فرمت آن مطابقت ندارد', 'ext' => ''];
    }
    // Check for PHP code in file content
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php|<\?=|<\?\s/i', $content)) {
        return ['valid' => false, 'error' => 'فایل حاوی کد مخرب است', 'ext' => ''];
    }
    return ['valid' => true, 'error' => '', 'ext' => $ext];
}
