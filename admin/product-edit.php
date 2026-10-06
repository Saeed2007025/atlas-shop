<?php
/**
 * Admin Product Edit
 */

$adminPage = 'products';
require_once __DIR__ . '/partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$db = get_db();

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) redirect('products.php');

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $catId = (int)$_POST['category_id'];
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)$_POST['stock'];
    $desc = $_POST['description'];
    $highlights = trim($_POST['highlights'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;

    $imagePath = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFilename = 'prod_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $newFilename)) {
            $imagePath = 'uploads/' . $newFilename;
        }
    }

    $stmt = $db->prepare("UPDATE products SET category_id = ?, name = ?, brand = ?, price = ?, discount_price = ?, stock = ?, description = ?, highlights = ?, is_featured = ?, is_new = ?, image = ? WHERE id = ?");
    $stmt->execute([$catId, $name, $brand, $price, $discountPrice, $stock, $desc, $highlights, $isFeatured, $isNew, $imagePath, $id]);

    redirect('products.php');
}
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>ویرایش محصول: <?php echo e($product['name']); ?></h2>
    <a href="products.php" style="color: var(--admin-muted);">بازگشت به لیست</a>
</div>

<div class="admin-card-box" style="max-width: 800px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>نام محصول *</label>
            <input type="text" name="name" value="<?php echo e($product['name']); ?>" class="form-control" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>دسته‌بندی *</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $product['category_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>برند</label>
                <input type="text" name="brand" value="<?php echo e($product['brand']); ?>" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>مشخصات کوتاه / نکات برجسته بالای قیمت (جداشده با ویرگول)</label>
            <input type="text" name="highlights" value="<?php echo e($product['highlights'] ?? ''); ?>" class="form-control" placeholder="مثال: میکروفون HD, بلوتوث ۵.۲, باتری ۳۰ ساعت, حذف نویز ANC">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>قیمت اصلی (تومان) *</label>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>قیمت با تخفیف (تومان)</label>
                <input type="number" name="discount_price" value="<?php echo $product['discount_price']; ?>" class="form-control">
            </div>

            <div class="form-group">
                <label>موجودی انبار *</label>
                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label>تصویر محصول</label>
            <input type="file" name="image" class="form-control image-upload-input" data-preview="prod-preview">
            <img id="prod-preview" src="../<?php echo e($product['image']); ?>" style="max-width: 120px; margin-top: 10px; border-radius: 8px;">
        </div>

        <div class="form-group">
            <label>توضیحات کامل محصول</label>
            <textarea name="description" class="form-control" rows="5"><?php echo e($product['description']); ?></textarea>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <label><input type="checkbox" name="is_featured" value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?>> محصول ویژه</label>
            <label><input type="checkbox" name="is_new" value="1" <?php echo $product['is_new'] ? 'checked' : ''; ?>> محصول جدید</label>
        </div>

        <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره تغییرات</button>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
