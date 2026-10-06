<?php
/**
 * Admin Shipping Methods Management
 * Allows adding/editing shipping methods, pricing, free threshold, and allowed provinces (e.g. Qom only or All)
 */

$adminPage = 'shipping';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

// Ensure shipping_methods table exists
$db->exec("CREATE TABLE IF NOT EXISTS `shipping_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `cost` DECIMAL(12, 0) NOT NULL DEFAULT 0,
  `free_threshold` DECIMAL(12, 0) DEFAULT 0,
  `allowed_provinces` VARCHAR(255) DEFAULT 'all',
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_method'])) {
        $title = trim($_POST['title']);
        $code = slugify(trim($_POST['code']));
        $desc = trim($_POST['description']);
        $cost = (float)$_POST['cost'];
        $freeThreshold = (float)($_POST['free_threshold'] ?? 0);
        $allowedProvinces = trim($_POST['allowed_provinces'] ?? 'all');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $editId = (int)($_POST['edit_id'] ?? 0);

        if ($editId > 0) {
            $stmt = $db->prepare("UPDATE shipping_methods SET title = ?, code = ?, description = ?, cost = ?, free_threshold = ?, allowed_provinces = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $code, $desc, $cost, $freeThreshold, $allowedProvinces, $isActive, $editId]);
        } else {
            $stmt = $db->prepare("INSERT INTO shipping_methods (title, code, description, cost, free_threshold, allowed_provinces, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $code, $desc, $cost, $freeThreshold, $allowedProvinces, $isActive]);
        }
        redirect('shipping-methods.php');
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM shipping_methods WHERE id = ?")->execute([$delId]);
    redirect('shipping-methods.php');
}

// Edit Mode
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM shipping_methods WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}

$methods = $db->query("SELECT * FROM shipping_methods ORDER BY display_order ASC, id ASC")->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت روش‌های ارسال و تنظیمات استان‌ها</h2>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 24px;">

    <!-- Add/Edit Form -->
    <div class="admin-card-box">
        <h3><?php echo $editItem ? 'ویرایش روش ارسال' : 'افزودن روش ارسال جدید'; ?></h3>
        <form method="POST" style="margin-top: 16px;">
            <input type="hidden" name="edit_id" value="<?php echo $editItem['id'] ?? 0; ?>">

            <div class="form-group">
                <label>عنوان روش ارسال *</label>
                <input type="text" name="title" value="<?php echo e($editItem['title'] ?? ''); ?>" placeholder="مثال: پیک موتوری (ویژه قم)" class="form-control" required>
            </div>

            <div class="form-group">
                <label>کد یکتا (Slug / Code) *</label>
                <input type="text" name="code" value="<?php echo e($editItem['code'] ?? ''); ?>" placeholder="مثال: courier_qom" class="form-control" required style="direction: ltr;">
            </div>

            <div class="form-group">
                <label>توضیحات کوتاه تحویل</label>
                <input type="text" name="description" value="<?php echo e($editItem['description'] ?? ''); ?>" placeholder="مثال: تحویل سریع در همان روز" class="form-control">
            </div>

            <div class="form-group">
                <label>هزینه ارسال (تومان) *</label>
                <input type="number" name="cost" value="<?php echo $editItem['cost'] ?? 35000; ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>سقف ارسال رایگان (تومان)</label>
                <input type="number" name="free_threshold" value="<?php echo $editItem['free_threshold'] ?? 1000000; ?>" placeholder="مثلاً ۱۰۰۰۰۰۰ (برای بدون ارسال رایگان عدد ۰ بگذارید)" class="form-control">
            </div>

            <div class="form-group">
                <label>استان‌های مجاز برای این روش *</label>
                <select name="allowed_provinces" class="form-control">
                    <option value="all" <?php echo ($editItem['allowed_provinces'] ?? 'all') === 'all' ? 'selected' : ''; ?>>همه استان‌های کشور (سراسر ایران)</option>
                    <option value="قم" <?php echo ($editItem['allowed_provinces'] ?? '') === 'قم' ? 'selected' : ''; ?>>فقط استان قم (ویژه پیک قم)</option>
                    <option value="تهران" <?php echo ($editItem['allowed_provinces'] ?? '') === 'تهران' ? 'selected' : ''; ?>>فقط استان تهران</option>
                    <option value="اصفهان" <?php echo ($editItem['allowed_provinces'] ?? '') === 'اصفهان' ? 'selected' : ''; ?>>فقط استان اصفهان</option>
                </select>
                <small style="font-size:11px; color:var(--admin-muted); display:block; margin-top:4px;">
                    نکته: اگر «فقط استان قم» انتخاب شود، اگر مشتری در تسویه‌حساب استان قم را انتخاب کند این روش نمایش داده می‌شود و اگر استان دیگری (مثلاً تهران) را انتخاب کند، این روش مخفی می‌شود.
                </small>
            </div>

            <div class="form-group">
                <label style="cursor: pointer;"><input type="checkbox" name="is_active" value="1" <?php echo ($editItem['is_active'] ?? 1) == 1 ? 'checked' : ''; ?>> فعال بودن این روش ارسال</label>
            </div>

            <button type="submit" name="save_method" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 12px;">
                <?php echo $editItem ? 'ذخیره تغییرات' : 'ثبت روش ارسال جدید'; ?>
            </button>
            <?php if ($editItem): ?>
                <a href="shipping-methods.php" style="display:block; text-align:center; color:var(--admin-muted); margin-top:10px; font-size:13px;">انصراف از ویرایش</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table List -->
    <div class="admin-card-box">
        <h3>لیست روش‌های ارسال</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>هزینه</th>
                    <th>ارسال رایگان بالای</th>
                    <th>استان مجاز</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($methods as $m): ?>
                    <tr>
                        <td style="font-weight: bold;">
                            <?php echo e($m['title']); ?>
                            <div style="font-size:11px; color:var(--admin-muted);"><?php echo e($m['description']); ?></div>
                        </td>
                        <td style="color: var(--admin-accent); font-weight: bold;"><?php echo format_price($m['cost']); ?></td>
                        <td><?php echo $m['free_threshold'] > 0 ? format_price($m['free_threshold']) : 'ندارد'; ?></td>
                        <td>
                            <?php if ($m['allowed_provinces'] === 'all'): ?>
                                <span class="badge-status status-paid">سراسر کشور</span>
                            <?php else: ?>
                                <span class="badge-status status-pending">فقط <?php echo e($m['allowed_provinces']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $m['is_active'] ? 'فعال' : 'غیرفعال'; ?></td>
                        <td>
                            <a href="shipping-methods.php?edit=<?php echo $m['id']; ?>" style="color: var(--admin-blue); margin-left: 8px; font-weight: bold;">ویرایش</a>
                            <a href="shipping-methods.php?delete=<?php echo $m['id']; ?>" onclick="return confirm('حذف روش ارسال؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
