<?php
/**
 * Admin Sales Reports & Analytics
 */

$adminPage = 'reports';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

$todaySales = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()")->fetchColumn();
$weekSales = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$monthSales = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$totalSales = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>گزارش‌های جامع فروش و درآمد</h2>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <span class="stat-label">فروش امروز</span>
        <div class="stat-value" style="color: var(--admin-accent);"><?php echo format_price($todaySales); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-label">فروش ۷ روز گذشته</span>
        <div class="stat-value"><?php echo format_price($weekSales); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-label">فروش ۳۰ روز گذشته</span>
        <div class="stat-value"><?php echo format_price($monthSales); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-label">مجموع درآمد کل</span>
        <div class="stat-value"><?php echo format_price($totalSales); ?></div>
    </div>
</div>

<div class="admin-card-box" style="margin-top: 24px;">
    <h3>خلاصه عملکرد فروشگاه اطلس شاپ</h3>
    <p style="color: var(--admin-muted); margin-top: 8px;">
        تمامی گزارش‌ها به‌صورت زنده بر اساس تراکنش‌های تاییدشده درگاه زرین‌پال محاسبه و به‌روزرسانی می‌گردند.
    </p>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
