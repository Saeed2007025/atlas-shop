<?php
/**
 * Zarinpal Payment Gateway Callback Handler with Atomic Stock Protection
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/zarinpal.php';

$orderId = (int)($_GET['order_id'] ?? 0);
$authority = trim($_GET['Authority'] ?? '');
$status = trim($_GET['Status'] ?? '');

$db = get_db();

if (!$orderId || !$db) {
    redirect(SITE_URL . '/failed?msg=' . urlencode('شناسه سفارش نامعتبر است.'));
}

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    redirect(SITE_URL . '/failed?msg=' . urlencode('سفارش یافت نشد.'));
}

if (!is_customer_logged_in() || current_customer()['id'] != $order['customer_id']) {
    redirect(SITE_URL . '/failed?msg=' . urlencode('شما دسترسی به این سفارش ندارید.'));
}

if ($order['payment_status'] !== 'pending') {
    redirect(SITE_URL . '/failed?msg=' . urlencode('این سفارش قبلاً پردازش شده است.'));
}

$stmt = $db->prepare("SELECT * FROM payment_transactions WHERE order_id = ? AND authority = ?");
$stmt->execute([$orderId, $authority]);
$transaction = $stmt->fetch();

if (!$transaction) {
    redirect(SITE_URL . '/failed?msg=' . urlencode('تراکنش نامعتبر است.'));
}

if ($status === 'OK' && !empty($authority)) {
    // Verify with Zarinpal API
    $verifyRes = zarinpal_verify($authority, $order['total_amount']);

    if ($verifyRes['success']) {
        $refId = $verifyRes['ref_id'];

        // Atomic Stock Deduction Check (Race Condition Protection)
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();

        foreach ($orderItems as $item) {
            if (!empty($item['product_id'])) {
                // Atomic deduction: Deduct ONLY if stock >= requested quantity
                $stockStmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);

                if ($stockStmt->rowCount() === 0) {
                    // Stock ran out during payment concurrency
                    error_log("Race Condition: Stock depleted for product ID " . $item['product_id']);
                }
            }
        }

        // Update Order & Transaction
        $db->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'processing' WHERE id = ?")->execute([$orderId]);
        $db->prepare("UPDATE payment_transactions SET status = 'success', ref_id = ? WHERE order_id = ? AND authority = ?")->execute([$refId, $orderId, $authority]);

        redirect(SITE_URL . '/success?order_id=' . $orderId . '&ref_id=' . $refId);
    } else {
        $msg = $verifyRes['message'] ?? 'پرداخت توسط درگاه زرین‌پال تایید نشد.';
        $db->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$orderId]);
        redirect(SITE_URL . '/failed?order_id=' . $orderId . '&msg=' . urlencode($msg));
    }
} else {
    $db->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$orderId]);
    redirect(SITE_URL . '/failed?order_id=' . $orderId . '&msg=' . urlencode('تراکنش توسط کاربر لغو شد یا ناموفق بود.'));
}
