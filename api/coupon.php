<?php
/**
 * Coupon API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$code = trim($_POST['code'] ?? '');
if (empty($code)) {
    json_response(['success' => false, 'message' => 'کد تخفیف را وارد کنید.'], 400);
}

$db = get_db();
if (!$db) json_response(['success' => false, 'message' => 'خطا در دیتابیس.'], 500);

$stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) {
    json_response(['success' => false, 'message' => 'کد تخفیف وارد شده معتبر نیست.'], 400);
}

if (!empty($coupon['end_date']) && strtotime($coupon['end_date']) < time()) {
    json_response(['success' => false, 'message' => 'مهلت استفاده از این کد تخفیف به پایان رسیده است.'], 400);
}

if ($coupon['times_used'] >= $coupon['usage_limit']) {
    json_response(['success' => false, 'message' => 'سقف استفاده از این کد تخفیف به تکمیل رسیده است.'], 400);
}

$_SESSION['coupon'] = [
    'id' => $coupon['id'],
    'code' => $coupon['code'],
    'type' => $coupon['type'],
    'value' => (float)$coupon['value'],
    'max_discount' => (float)$coupon['max_discount']
];

json_response([
    'success' => true,
    'message' => 'کد تخفیف با موفقیت اعمال شد.',
    'coupon' => $_SESSION['coupon']
]);
