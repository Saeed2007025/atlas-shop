<?php
/**
 * Customer Previous Orders Standalone Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'orders';
$seoParams = [
    'title' => 'سفارش‌های قبلی شما',
    'description' => 'پیگیری و مشاهده لیست سفارش‌های قبلی و دریافت فاکتور خرید.'
];

// Guest Check: Redirect to login if not logged in
if (!is_customer_logged_in()) {
    redirect(SITE_URL . '/login?redirect=orders');
}

$customer = current_customer();
$db = get_db();

$previousOrders = [];
if ($customer && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? OR receiver_mobile = ? ORDER BY id DESC");
        $stmt->execute([$customer['id'], $customer['mobile']]);
        $previousOrders = $stmt->fetchAll();
    } catch (Exception $e) {}
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 32px; margin-bottom: 60px; max-width: 900px;">

    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>سفارش‌های قبلی شما</span>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <h1 style="font-size: 20px; font-weight: 800; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px; color: var(--primary); display: flex; align-items: center; gap: 10px;">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>سفارش‌های قبلی شما (<?php echo e($customer['full_name'] ?: $customer['mobile']); ?>)</span>
        </h1>

        <?php if (empty($previousOrders)): ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--muted);">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h3>هنوز سفارشی توسط شما ثبت نشده است.</h3>
                <p style="margin-top: 8px;"><a href="shop" class="action-btn btn-login" style="display: inline-flex; margin-top: 12px;">مشاهده و خرید محصولات</a></p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($previousOrders as $po): ?>
                    <div style="background: var(--surface-soft); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <strong style="font-size: 15px; color: var(--text);">شماره سفارش: <code>#<?php echo e($po['order_number']); ?></code></strong>
                                <span style="font-size: 12px; color: var(--muted); margin-right: 12px;"><?php echo to_persian_num(date('Y/m/d H:i', strtotime($po['created_at']))); ?></span>
                            </div>
                            <div>
                                <?php if ($po['payment_status'] === 'paid'): ?>
                                    <span style="background: rgba(45, 154, 91, 0.15); color: var(--success); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">پرداخت شده</span>
                                <?php else: ?>
                                    <span style="background: rgba(245, 158, 11, 0.15); color: #F59E0B; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">در انتظار پرداخت</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
                            گیرنده: <?php echo e($po['receiver_name']); ?> | آدرس: <?php echo e($po['state']); ?> - <?php echo e($po['city']); ?>، <?php echo e($po['address']); ?>

                        </div>

                        <div style="font-size: 14px; color: var(--text); display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border); padding-top: 12px;">
                            <span>مبلغ پرداختی: <strong style="color: var(--primary); font-size: 16px;"><?php echo format_price($po['total_amount']); ?></strong></span>
                            <a href="success?order_id=<?php echo $po['id']; ?>" class="action-btn btn-login" style="padding: 8px 16px; font-size: 13px;">مشاهده فاکتور و رسید ↗</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
