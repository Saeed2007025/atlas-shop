<?php
/**
 * Admin Coupons Management
 */

$adminPage = 'coupons';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = trim($_POST['type']);
    $val = (float)$_POST['value'];
    $minOrder = (float)$_POST['min_order_amount'];
    $maxDiscount = (float)$_POST['max_discount'];

    $stmt = $db->prepare("INSERT INTO coupons (code, type, value, min_order_amount, max_discount, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, 100, 1)");
    $stmt->execute([$code, $type, $val, $minOrder, $maxDiscount]);
    redirect('coupons.php');
}

if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$delId]);
    redirect('coupons.php');
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form -->
    <div class="admin-card-box">
        <h3>ایجاد کد تخفیف جدید</h3>
        <form method="POST" style="margin-top: 16px;">
            <div class="form-group">
                <label>کد تخفیف (مثال: SUMMER20) *</label>
                <input type="text" name="code" class="form-control" required style="text-transform: uppercase;">
            </div>
            <div class="form-group">
                <label>نوع تخفیف</label>
                <select name="type" class="form-control">
                    <option value="percent">درصدی (٪)</option>
                    <option value="fixed">مبلغ ثابت (تومان)</option>
                </select>
            </div>
            <div class="form-group">
                <label>مقدار (درصد یا تومان) *</label>
                <input type="number" name="value" class="form-control" required>
            </div>
            <div class="form-group">
                <label>حداقل مبلغ سفارش (تومان)</label>
                <input type="number" name="min_order_amount" value="0" class="form-control">
            </div>
            <div class="form-group">
                <label>سقف تخفیف (برای درصدی)</label>
                <input type="number" name="max_discount" value="0" class="form-control">
            </div>
            <button type="submit" name="add_coupon" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 10px;">ذخیره کد تخفیف</button>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card-box">
        <h3>لیست کدهای تخفیف</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>کد</th>
                    <th>نوع</th>
                    <th>مقدار</th>
                    <th>حداقل خرید</th>
                    <th>تعداد استفاده</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $cp): ?>
                    <tr>
                        <td><code><?php echo e($cp['code']); ?></code></td>
                        <td><?php echo $cp['type'] === 'percent' ? 'درصدی' : 'مبلغ ثابت'; ?></td>
                        <td style="font-weight: bold; color: var(--admin-accent);">
                            <?php echo $cp['type'] === 'percent' ? to_persian_num($cp['value']) . '٪' : format_price($cp['value']); ?>
                        </td>
                        <td><?php echo format_price($cp['min_order_amount']); ?></td>
                        <td><?php echo to_persian_num($cp['times_used']); ?> بار</td>
                        <td>
                            <a href="coupons.php?delete=<?php echo $cp['id']; ?>" onclick="return confirm('حذف کد؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
