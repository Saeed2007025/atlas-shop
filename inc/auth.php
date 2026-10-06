<?php
/**
 * Authentication Module
 * Customer Mobile OTP & Admin Login with Brute Force Protection
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Check if customer logged in
function is_customer_logged_in() {
    return !empty($_SESSION['customer_id']);
}

function current_customer() {
    if (!is_customer_logged_in()) return null;
    $db = get_db();
    if (!$db) return null;
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch() ?: null;
}

// Validate Iranian Mobile Number
function sanitize_mobile($mobile) {
    $mobile = to_english_num($mobile);
    $mobile = preg_replace('/[^\d+]/', '', $mobile);
    if (strpos($mobile, '+98') === 0) {
        $mobile = '0' . substr($mobile, 3);
    } elseif (strpos($mobile, '98') === 0) {
        $mobile = '0' . substr($mobile, 2);
    }
    if (preg_match('/^09\d{9}$/', $mobile)) {
        return $mobile;
    }
    return false;
}

// Generate & Send OTP (with Rate-Limiting protection)
function send_otp($mobile) {
    $mobile = sanitize_mobile($mobile);
    if (!$mobile) {
        return ['success' => false, 'message' => 'شماره موبایل وارد شده معتبر نیست. (مثال: 09123456789)'];
    }

    // Rate limit check (60s cooldown between OTP requests)
    $lastOtpTime = $_SESSION['last_otp_time_' . $mobile] ?? 0;
    if (time() - $lastOtpTime < 60) {
        $rem = 60 - (time() - $lastOtpTime);
        return ['success' => false, 'message' => "لطفاً $rem ثانیه دیگر مجدداً تلاش کنید."];
    }

    $db = get_db();
    if (!$db) {
        return ['success' => false, 'message' => 'خطا در ارتباط با دیتابیس.'];
    }

    $code = (string)rand(10000, 99999);
    $mockMode = get_setting('sms_mock_mode', '1');
    if ($mockMode == '1' && defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        // Mock mode only allowed in DEVELOPMENT_MODE
    } else {
        $mockMode = '0';
    }

    $expiresAt = date('Y-m-d H:i:s', strtotime('+3 minutes'));

    // Invalidate old OTPs
    $stmt = $db->prepare("UPDATE customer_otps SET is_used = 1 WHERE mobile = ?");
    $stmt->execute([$mobile]);

    // Insert new OTP
    // We add attempts column dynamically if we didn't add to schema
    try {
        $db->exec("ALTER TABLE customer_otps ADD COLUMN attempts INT DEFAULT 0");
    } catch (Exception $e) {}

    $stmt = $db->prepare("INSERT INTO customer_otps (mobile, code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$mobile, $code, $expiresAt]);

    $_SESSION['last_otp_time_' . $mobile] = time();

    if ($mockMode == '1') {
        return [
            'success' => true,
            'message' => 'کد تایید ارسال شد (حالت تست: کد شما ' . $code . ' است)',
            'code' => $code
        ];
    } else {
        return [
            'success' => true,
            'message' => 'کد تایید به شماره ' . e($mobile) . ' پیامک شد.'
        ];
    }
}

// Verify OTP
function verify_otp($mobile, $code) {
    $mobile = sanitize_mobile($mobile);
    $code = to_english_num(trim($code));

    if (!$mobile) {
        return ['success' => false, 'message' => 'شماره موبایل نامعتبر است.'];
    }

    $db = get_db();
    if (!$db) {
        return ['success' => false, 'message' => 'خطا در ارتباط با دیتابیس.'];
    }

    $stmt = $db->prepare("SELECT * FROM customer_otps WHERE mobile = ? AND is_used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute([$mobile]);
    $otp = $stmt->fetch();

    if (!$otp) {
        return ['success' => false, 'message' => 'کد تایید منقضی شده یا درخواست نشده است.'];
    }

    $attempts = $otp['attempts'] ?? 0;
    if ($attempts >= 5) {
        $db->prepare("UPDATE customer_otps SET is_used = 1 WHERE id = ?")->execute([$otp['id']]);
        return ['success' => false, 'message' => 'تعداد دفعات مجاز به پایان رسید. لطفاً کد جدید دریافت کنید.'];
    }

    if ($otp['code'] !== $code) {
        $db->prepare("UPDATE customer_otps SET attempts = attempts + 1 WHERE id = ?")->execute([$otp['id']]);
        return ['success' => false, 'message' => 'کد تایید اشتباه است.'];
    }

    // Mark OTP as used
    $db->prepare("UPDATE customer_otps SET is_used = 1 WHERE id = ?")->execute([$otp['id']]);

    // Check or create customer
    $stmt = $db->prepare("SELECT * FROM customers WHERE mobile = ?");
    $stmt->execute([$mobile]);
    $customer = $stmt->fetch();

    if (!$customer) {
        $stmt = $db->prepare("INSERT INTO customers (mobile, created_at) VALUES (?, NOW())");
        $stmt->execute([$mobile]);
        $customerId = $db->lastInsertId();
    } else {
        $customerId = $customer['id'];
    }

    session_regenerate_id(true); // Prevent Session Fixation
    $_SESSION['customer_id'] = $customerId;
    $_SESSION['customer_mobile'] = $mobile;

    return ['success' => true, 'message' => 'ورود با موفقیت انجام شد.'];
}

// Customer Logout
function customer_logout() {
    unset($_SESSION['customer_id'], $_SESSION['customer_mobile']);
}

// Admin Authentication
function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function current_admin() {
    if (!is_admin_logged_in()) return null;
    $db = get_db();
    if (!$db) return null;
    $stmt = $db->prepare("SELECT id, username, full_name, email FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function require_admin() {
    if (!is_admin_logged_in()) {
        redirect(SITE_URL . '/panel-adminweb/login.php');
    }
}

// Admin Login with Brute Force Rate Limiting
function admin_login($username, $password) {
    $db = get_db();
    if (!$db) {
        return ['success' => false, 'message' => 'خطا در اتصال به دیتابیس.'];
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    try {
        $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            ip VARCHAR(45) PRIMARY KEY,
            attempts INT DEFAULT 0,
            lockout_until DATETIME DEFAULT NULL
        )");
    } catch (Exception $e) {}

    $stmt = $db->prepare("SELECT attempts, lockout_until FROM login_attempts WHERE ip = ?");
    $stmt->execute([$ip]);
    $attemptRecord = $stmt->fetch();

    if ($attemptRecord) {
        if (!empty($attemptRecord['lockout_until']) && strtotime($attemptRecord['lockout_until']) > time()) {
            $remMinutes = ceil((strtotime($attemptRecord['lockout_until']) - time()) / 60);
            return ['success' => false, 'message' => "تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً $remMinutes دقیقه دیگر تلاش کنید."];
        }
        if (!empty($attemptRecord['lockout_until']) && strtotime($attemptRecord['lockout_until']) <= time()) {
            $db->prepare("UPDATE login_attempts SET attempts = 0, lockout_until = NULL WHERE ip = ?")->execute([$ip]);
            $attemptRecord['attempts'] = 0;
        }
    }

    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $db->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
        
        session_regenerate_id(true); // Prevent Session Fixation
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        return ['success' => true];
    }

    // Increment failed attempts
    $newAttempts = ($attemptRecord['attempts'] ?? 0) + 1;
    if ($newAttempts >= 5) {
        $lockoutUntil = date('Y-m-d H:i:s', time() + 900);
        if ($attemptRecord) {
            $db->prepare("UPDATE login_attempts SET attempts = ?, lockout_until = ? WHERE ip = ?")->execute([$newAttempts, $lockoutUntil, $ip]);
        } else {
            $db->prepare("INSERT INTO login_attempts (ip, attempts, lockout_until) VALUES (?, ?, ?)")->execute([$ip, $newAttempts, $lockoutUntil]);
        }
        return ['success' => false, 'message' => "تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً 15 دقیقه دیگر تلاش کنید."];
    } else {
        if ($attemptRecord) {
            $db->prepare("UPDATE login_attempts SET attempts = ? WHERE ip = ?")->execute([$newAttempts, $ip]);
        } else {
            $db->prepare("INSERT INTO login_attempts (ip, attempts) VALUES (?, ?)")->execute([$ip, $newAttempts]);
        }
    }

    return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است.'];
}

function admin_logout() {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name']);
}
