<?php
/**
 * Order Payment Failed Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$msg = trim($_GET['msg'] ?? 'تراکنش پرداخت ناموفق بود یا توسط کاربر لغو شد.');

$seoParams = [
    'title' => 'پرداخت ناموفق',
    'description' => 'پرداخت سفارش نا تمام ماند.'
];

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 600px; margin: 60px auto;">

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 40px; text-align: center; box-shadow: var(--shadow);">
        <div style="width: 64px; height: 64px; background: rgba(215, 80, 93, 0.15); color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
            <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>

        <h1 style="font-size: 22px; color: var(--danger); font-weight: 800; margin-bottom: 8px;">پرداخت ناموفق بود!</h1>
        <p style="color: var(--muted); font-size: 14px; margin-bottom: 24px;"><?php echo e($msg); ?></p>

        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="checkout.php" class="action-btn btn-login" style="padding: 12px 24px;">تلاش مجدد در checkout</a>
            <a href="cart.php" class="action-btn" style="background: var(--surface-soft); border: 1px solid var(--border); padding: 12px 24px;">مشاهده سبد خرید</a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
