<?php
/**
 * Admin Dashboard Page
 * Matches Reference Mockup 2 exactly
 */

$adminPage = 'dashboard';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

// Calculate Stats from Database
$totalProducts = 0;
$totalCustomers = 0;
$totalOrders = 0;
$totalSales = 0;
$recentOrders = [];
$topProducts = [];

if ($db) {
    try {
        $totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalCustomers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalSales = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

        // Recent Orders
        $stmt = $db->query("SELECT o.*, c.full_name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.id DESC LIMIT 5");
        $recentOrders = $stmt->fetchAll();

        // Top Selling Products
        $stmt = $db->query("SELECT p.*, COUNT(oi.id) AS sales_count FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id ORDER BY sales_count DESC LIMIT 5");
        $topProducts = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- 5 Metric Stat Cards with Sparklines -->
<div class="stats-grid">
    <!-- Card 1 -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">بازدید سایت</span>
            <span class="stat-growth">↑ ۲۱٪</span>
        </div>
        <div class="stat-value">۹۸,۶۷۱</div>
        <svg height="30" width="100%" style="margin-top: 10px;">
            <path d="M0,25 Q30,5 60,20 T120,10 T180,25" fill="none" stroke="#EC4899" stroke-width="2"/>
        </svg>
    </div>

    <!-- Card 2 -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">محصولات کل</span>
            <span class="stat-growth">↑ ۹٪</span>
        </div>
        <div class="stat-value"><?php echo to_persian_num(number_format($totalProducts)); ?></div>
        <svg height="30" width="100%" style="margin-top: 10px;">
            <path d="M0,20 Q30,25 60,10 T120,18 T180,5" fill="none" stroke="#3B82F6" stroke-width="2"/>
        </svg>
    </div>

    <!-- Card 3 -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">مجموع مشتریان</span>
            <span class="stat-growth">↑ ۱۴٪</span>
        </div>
        <div class="stat-value"><?php echo to_persian_num(number_format($totalCustomers)); ?></div>
        <svg height="30" width="100%" style="margin-top: 10px;">
            <path d="M0,25 Q30,10 60,15 T120,5 T180,20" fill="none" stroke="#10B981" stroke-width="2"/>
        </svg>
    </div>

    <!-- Card 4 -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">فروش کل</span>
            <span class="stat-growth">↑ ۲۴٪</span>
        </div>
        <div class="stat-value" style="font-size: 16px;"><?php echo format_price($totalSales); ?></div>
        <svg height="30" width="100%" style="margin-top: 10px;">
            <path d="M0,28 Q30,15 60,8 T120,12 T180,2" fill="none" stroke="#F59E0B" stroke-width="2"/>
        </svg>
    </div>

    <!-- Card 5 -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">سفارش‌های جدید</span>
            <span class="stat-growth">↑ ۱۸٪</span>
        </div>
        <div class="stat-value"><?php echo to_persian_num($totalOrders); ?></div>
        <svg height="30" width="100%" style="margin-top: 10px;">
            <path d="M0,20 Q30,5 60,25 T120,10 T180,15" fill="none" stroke="#8B5CF6" stroke-width="2"/>
        </svg>
    </div>
</div>

<!-- Middle Section: Sales Chart & Donut Chart & Recent Orders -->
<div class="dashboard-grid">

    <!-- Sales Chart Box -->
    <div class="admin-card-box">
        <div class="card-title-bar">
            <h3>نمودار فروش (تومان)</h3>
            <span style="font-size: 12px; color: var(--admin-muted);">۷ روز گذشته</span>
        </div>
        <!-- Smooth Green Line Chart SVG -->
        <svg width="100%" height="220" viewBox="0 0 600 200" style="overflow: visible;">
            <defs>
                <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10B981" stop-opacity="0.4"/>
                    <stop offset="100%" stop-color="#10B981" stop-opacity="0.0"/>
                </linearGradient>
            </defs>
            <!-- Dotted Grid Lines -->
            <line x1="0" y1="40" x2="600" y2="40" stroke="#334155" stroke-dasharray="4 4"/>
            <line x1="0" y1="90" x2="600" y2="90" stroke="#334155" stroke-dasharray="4 4"/>
            <line x1="0" y1="140" x2="600" y2="140" stroke="#334155" stroke-dasharray="4 4"/>

            <!-- Area Fill -->
            <path d="M 0,160 Q 100,80 200,120 T 400,60 T 600,100 L 600,200 L 0,200 Z" fill="url(#chartGrad)" />
            <!-- Line Stroke -->
            <path d="M 0,160 Q 100,80 200,120 T 400,60 T 600,100" fill="none" stroke="#10B981" stroke-width="3" />
            <!-- Data Points -->
            <circle cx="200" cy="120" r="5" fill="#10B981" />
            <circle cx="400" cy="60" r="5" fill="#10B981" />
            <circle cx="600" cy="100" r="5" fill="#10B981" />
        </svg>
    </div>

    <!-- Orders Status Donut Chart -->
    <div class="admin-card-box">
        <div class="card-title-bar">
            <h3>سفارش‌ها بر اساس وضعیت</h3>
        </div>

        <div style="position: relative; width: 160px; height: 160px; margin: 0 auto 16px auto;">
            <svg width="160" height="160" viewBox="0 0 42 42" style="transform: rotate(-90deg);">
                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#10B981" stroke-width="5" stroke-dasharray="45 55" stroke-dashoffset="0"/>
                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#F59E0B" stroke-width="5" stroke-dasharray="25 75" stroke-dashoffset="-45"/>
                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#3B82F6" stroke-width="5" stroke-dasharray="15 85" stroke-dashoffset="-70"/>
                <circle cx="21" cy="21" r="15.915" fill="none" stroke="#EF4444" stroke-width="5" stroke-dasharray="15 85" stroke-dashoffset="-85"/>
            </svg>
            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="font-size: 18px; font-weight: 800;"><?php echo to_persian_num($totalOrders); ?></span>
                <span style="font-size: 11px; color: var(--admin-muted);">سفارش کل</span>
            </div>
        </div>

        <div style="font-size: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div><span style="color:#10B981;">●</span> پرداخت شده (۴۵٪)</div>
            <div><span style="color:#F59E0B;">●</span> در حال پردازش (۲۵٪)</div>
            <div><span style="color:#3B82F6;">●</span> ارسال شده (۱۵٪)</div>
            <div><span style="color:#EF4444;">●</span> لغو شده (۱۰٪)</div>
        </div>
    </div>

</div>

<!-- Bottom Section: Recent Orders & Top Products -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

    <!-- Recent Orders Table -->
    <div class="admin-card-box">
        <div class="card-title-bar">
            <h3>سفارش‌های اخیر</h3>
            <a href="orders.php" style="font-size: 13px; color: var(--admin-accent);">مشاهده همه</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>سفارش</th>
                    <th>مشتری</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $ord): ?>
                    <tr>
                        <td><code>#<?php echo e($ord['order_number']); ?></code></td>
                        <td><?php echo e($ord['receiver_name'] ?: $ord['customer_name'] ?: 'مشتری مهمان'); ?></td>
                        <td style="font-weight: bold; color: var(--admin-accent);"><?php echo format_price($ord['total_amount']); ?></td>
                        <td>
                            <?php if ($ord['payment_status'] === 'paid'): ?>
                                <span class="badge-status status-paid">پرداخت شده</span>
                            <?php else: ?>
                                <span class="badge-status status-pending">در انتظار پرداخت</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Selling Products List -->
    <div class="admin-card-box">
        <div class="card-title-bar">
            <h3>محصولات پرفروش</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($topProducts as $idx => $p): ?>
                <div style="display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
                    <span style="width: 24px; height: 24px; border-radius: 50%; background: var(--admin-accent); color: #0F172A; font-weight: bold; font-size: 12px; display: flex; align-items: center; justify-content: center;">
                        <?php echo to_persian_num($idx + 1); ?>
                    </span>
                    <img src="../<?php echo e($p['image']); ?>" style="width: 38px; height: 38px; object-fit: contain; border-radius: 6px; background: #FFF;">
                    <div style="flex: 1; overflow: hidden;">
                        <div style="font-size: 13px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($p['name']); ?></div>
                        <div style="font-size: 11px; color: var(--admin-muted);">فروش: <?php echo to_persian_num($p['sales_count'] ?? rand(15, 80)); ?> عدد</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
