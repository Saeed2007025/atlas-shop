<?php
/**
 * Admin Orders Management
 */

$adminPage = 'orders';
require_once __DIR__ . '/partials/header.php';

$db = get_db();
$statusFilter = $_GET['status'] ?? 'all';

// Status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['order_status']);
    $stmt = $db->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    set_flash('success', 'وضعیت سفارش با موفقیت به روز شد.');
    redirect('orders.php?status=' . $statusFilter);
}

$where = ["1=1"];
$params = [];
if ($statusFilter !== 'all') {
    $where[] = "order_status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(" AND ", $where);
$orders = $db->prepare("SELECT o.*, c.full_name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $whereClause ORDER BY o.id DESC");
$orders->execute($params);
$orderList = $orders->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت سفارش‌ها</h2>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px;">
    <a href="orders.php?status=all" class="btn-green-add <?php echo $statusFilter==='all'?'':'btn-secondary'; ?>" style="<?php echo $statusFilter==='all'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">همه</a>
    <a href="orders.php?status=pending" class="btn-green-add" style="<?php echo $statusFilter==='pending'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">در انتظار پرداخت</a>
    <a href="orders.php?status=paid" class="btn-green-add" style="<?php echo $statusFilter==='paid'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">پرداخت شده</a>
    <a href="orders.php?status=shipped" class="btn-green-add" style="<?php echo $statusFilter==='shipped'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">ارسال شده</a>
    <a href="orders.php?status=completed" class="btn-green-add" style="<?php echo $statusFilter==='completed'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">تکمیل شده</a>
    <a href="orders.php?status=canceled" class="btn-green-add" style="<?php echo $statusFilter==='canceled'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">لغو شده</a>
</div>

<div class="admin-card-box">
    <table class="admin-table">
        <thead>
            <tr>
                <th>شماره سفارش</th>
                <th>تحویل‌گیرنده</th>
                <th>موبایل</th>
                <th>شهر / استان</th>
                <th>مبلغ کل</th>
                <th>وضعیت پرداخت</th>
                <th>وضعیت سفارش</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderList as $ord): ?>
                <tr>
                    <td><code>#<?php echo e($ord['order_number']); ?></code></td>
                    <td><?php echo e($ord['receiver_name']); ?></td>
                    <td><?php echo e($ord['receiver_mobile']); ?></td>
                    <td><?php echo e($ord['state']); ?> - <?php echo e($ord['city']); ?></td>
                    <td style="font-weight: bold; color: var(--admin-accent);"><?php echo format_price($ord['total_amount']); ?></td>
                    <td>
                        <?php if ($ord['payment_status'] === 'paid'): ?>
                            <span class="badge-status status-paid">پرداخت شده</span>
                        <?php else: ?>
                            <span class="badge-status status-pending">در انتظار</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: flex; gap: 6px;">
                            <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                            <select name="order_status" class="form-control" style="padding: 4px 8px; font-size: 12px; width: auto;" onchange="this.form.submit()">
                                <option value="pending" <?php echo $ord['order_status']==='pending'?'selected':''; ?>>در انتظار</option>
                                <option value="processing" <?php echo $ord['order_status']==='processing'?'selected':''; ?>>در حال پردازش</option>
                                <option value="shipped" <?php echo $ord['order_status']==='shipped'?'selected':''; ?>>ارسال شده</option>
                                <option value="completed" <?php echo $ord['order_status']==='completed'?'selected':''; ?>>تکمیل شده</option>
                                <option value="canceled" <?php echo $ord['order_status']==='canceled'?'selected':''; ?>>لغو شده</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td>
                        <button type="button" onclick="alert('سفارش #<?php echo e($ord['order_number']); ?>\nآدرس: <?php echo e($ord['address']); ?>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px;">جزئیات</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
