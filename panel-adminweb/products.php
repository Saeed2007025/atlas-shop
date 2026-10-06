<?php
/**
 * Admin Products Management
 */

$adminPage = 'products';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf();
    $delId = (int)$_POST['delete_id'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
    redirect('products.php?deleted=1');
}

$stmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت و لیست محصولات (<?php echo to_persian_num(count($products)); ?> کالا)</h2>
    <a href="product-create.php" class="btn-green-add">+ افزودن محصول جدید</a>
</div>

<?php if (isset($_GET['published'])): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
        ✔ محصول جدید با موفقیت ذخیره و منتشر شد.
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
        ✔ تغییرات محصول با موفقیت ذخیره گردید.
    </div>
<?php endif; ?>

<div class="admin-card-box">
    <table class="admin-table">
        <thead>
            <tr>
                <th>تصویر</th>
                <th>نام محصول</th>
                <th>دسته‌بندی</th>
                <th>برند</th>
                <th>قیمت اصلی</th>
                <th>قیمت با تخفیف</th>
                <th>موجودی</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="8" style="text-align:center; color:var(--admin-muted);">محصولی ثبت نشده است.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo e($p['image']); ?>" style="width: 44px; height: 44px; object-fit: contain; border-radius: 6px; background: #FFF; border: 1px solid var(--admin-border);">
                        </td>
                        <td style="font-weight: bold; color: var(--admin-text);"><?php echo e($p['name']); ?></td>
                        <td><?php echo e($p['category_name'] ?: 'بدون دسته'); ?></td>
                        <td><?php echo e($p['brand'] ?: '-'); ?></td>
                        <td><?php echo format_price($p['price']); ?></td>
                        <td style="color: var(--admin-accent); font-weight: bold;"><?php echo $p['discount_price'] > 0 ? format_price($p['discount_price']) : '-'; ?></td>
                        <td><?php echo to_persian_num($p['stock']); ?> عدد</td>
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" style="color: var(--admin-blue); margin-left: 12px; font-weight: bold;">ویرایش</a>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" onclick="return confirm('آیا از حذف این محصول اطمینان دارید؟')" style="background:none; border:none; color: var(--admin-red); font-weight: bold; cursor:pointer; font-family: inherit; font-size: inherit; padding: 0;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
