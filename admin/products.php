<?php
/**
 * Admin Products Management
 */

$adminPage = 'products';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
    redirect('products.php');
}

$stmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $stmt->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت محصولات</h2>
    <a href="product-create.php" class="btn-green-add">+ افزودن محصول جدید</a>
</div>

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
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <img src="../<?php echo e($p['image']); ?>" style="width: 44px; height: 44px; object-fit: contain; border-radius: 6px; background: #FFF;">
                    </td>
                    <td style="font-weight: bold;"><?php echo e($p['name']); ?></td>
                    <td><?php echo e($p['category_name']); ?></td>
                    <td><?php echo e($p['brand']); ?></td>
                    <td><?php echo format_price($p['price']); ?></td>
                    <td style="color: var(--admin-accent); font-weight: bold;"><?php echo $p['discount_price'] > 0 ? format_price($p['discount_price']) : '-'; ?></td>
                    <td><?php echo to_persian_num($p['stock']); ?> عدد</td>
                    <td>
                        <a href="product-edit.php?id=<?php echo $p['id']; ?>" style="color: var(--admin-blue); margin-left: 12px; font-weight: bold;">ویرایش</a>
                        <a href="products.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('آیا از حذف این محصول اطمینان دارید؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
