<?php
/**
 * Order Payment Success & Official Invoice Receipt
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/seo.php';

$orderId = (int)($_GET['order_id'] ?? 0);
$refId = trim($_GET['ref_id'] ?? '');

if (!is_customer_logged_in()) {
    redirect(SITE_URL . '/login.php?redirect=' . urlencode('success.php?order_id=' . $orderId . '&ref_id=' . $refId));
}

$db = get_db();
$order = null;
$orderItems = [];

if ($orderId && $db) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        if ($order['customer_id'] != current_customer()['id']) {
            http_response_code(403);
            die("شما دسترسی به این سفارش ندارید.");
        }

        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
    }
}

$siteName = get_setting('site_name', 'اطلس شاپ');
$phone = get_setting('contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸');
$mobile = get_setting('contact_mobile', '۰۹۹۰۱۲۳۴۵۶۷');

$seoParams = [
    'title' => 'رسید پرداخت موفق سفارش',
    'description' => 'سفارش شما با موفقیت ثبت و پرداخت شد.'
];

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 720px; margin: 40px auto 80px auto;">

    <!-- Printable Invoice Receipt Card -->
    <div id="invoice-card" style="background: #FFFFFF; border: 2px solid var(--border); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow); color: #17233D;">
        
        <!-- Header Receipt Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary); padding-bottom: 16px; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 4px;"><?php echo e($siteName); ?></h1>
                <div style="font-size: 13px; color: var(--success); font-weight: bold;">✔ رسید پرداخت آنلاین و فاکتور فروش</div>
            </div>
            <div style="text-align: left; font-size: 12px; color: var(--muted);">
                <div>تاریخ: <?php echo to_persian_num(date('Y/m/d H:i')); ?></div>
                <div style="font-weight: bold; color: var(--text); margin-top: 4px;">شماره فاکتور: <code>#<?php echo e($order['order_number'] ?? 'ATL-' . $orderId); ?></code></div>
            </div>
        </div>

        <?php if ($order): ?>
            <!-- Transaction & Customer Info Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: var(--surface-soft); padding: 16px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 24px; font-size: 13px; line-height: 1.8;">
                <div>
                    <div><strong>تحویل‌گیرنده:</strong> <?php echo e($order['receiver_name']); ?></div>
                    <div><strong>شماره همراه:</strong> <code><?php echo e($order['receiver_mobile']); ?></code></div>
                    <div><strong>استان / شهر:</strong> <?php echo e($order['state']); ?> - <?php echo e($order['city']); ?></div>
                </div>
                <div>
                    <div><strong>کد پیگیری درگاه (Ref ID):</strong> <code style="color: var(--primary); font-weight: bold;"><?php echo e($refId ?: 'تراکنش تایید شده'); ?></code></div>
                    <div><strong>وضعیت پرداخت:</strong> <span style="color: var(--success); font-weight: bold;">پرداخت موفق با زرین‌پال</span></div>
                    <div><strong>نحوه ارسال:</strong> <?php echo e($order['shipping_method'] === 'post' ? 'پست پیشتاز' : 'پیک اکسپرس'); ?></div>
                </div>
            </div>

            <!-- Purchased Items Table -->
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 15px; font-weight: bold; margin-bottom: 12px; color: var(--text);">اقلام سفارش</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: var(--primary-soft); color: var(--primary); font-weight: bold;">
                            <th style="padding: 10px; text-align: right; border-radius: 6px 0 0 6px;">شرح کالا</th>
                            <th style="padding: 10px; text-align: center;">قیمت واحد</th>
                            <th style="padding: 10px; text-align: center;">تعداد</th>
                            <th style="padding: 10px; text-align: left; border-radius: 0 6px 6px 0;">جمع کل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 10px; font-weight: bold;"><?php echo e($item['product_name']); ?></td>
                                <td style="padding: 10px; text-align: center;"><?php echo format_price($item['price']); ?></td>
                                <td style="padding: 10px; text-align: center; font-weight: bold;"><?php echo to_persian_num($item['quantity']); ?></td>
                                <td style="padding: 10px; text-align: left; font-weight: bold; color: var(--primary);"><?php echo format_price($item['total_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Receipt Totals Box -->
            <div style="background: var(--surface-soft); padding: 16px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 24px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>مجموع کالاها:</span>
                    <span><?php echo format_price($order['subtotal']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>هزینه ارسال:</span>
                    <span><?php echo $order['shipping_cost'] == 0 ? 'رایگان' : format_price($order['shipping_cost']); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--danger);">
                        <span>تخفیف:</span>
                        <span>-<?php echo format_price($order['discount_amount']); ?></span>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--primary); padding-top: 12px; font-size: 16px; font-weight: 800; color: var(--primary);">
                    <span>مبلغ کل پرداختی:</span>
                    <span><?php echo format_price($order['total_amount']); ?></span>
                </div>
            </div>

            <!-- Support & Order Tracking Phone Info Footer -->
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; color: #92400E; padding: 14px; border-radius: var(--radius); font-size: 13px; text-align: center; line-height: 1.8;">
                <strong>شماره پیگیری و پشتیبانی تلفنی سفارش:</strong><br>
                تلفن ثابت: <code><?php echo e($phone); ?></code> | شماره همراه: <code><?php echo e($mobile); ?></code>
            </div>

        <?php else: ?>
            <p style="text-align: center; color: var(--muted); padding: 20px;">اطلاعات فاکتور یافت نشد.</p>
        <?php endif; ?>

    </div>

    <!-- Print & Home Action Buttons -->
    <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
        <button type="button" onclick="window.print()" class="action-btn" style="background: var(--surface); border: 1px solid var(--border); padding: 12px 24px; font-weight: bold;">
            🖨️ چاپ یا دریافت فاکتور
        </button>
        <a href="<?php echo SITE_URL; ?>/" class="action-btn btn-login" style="padding: 12px 28px;">بازگشت به فروشگاه</a>
    </div>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
