<?php
/**
 * Admin Product Create - Rock Solid Upload & Insertion
 */

$adminPage = 'products';
require_once __DIR__ . '/partials/header.php';

$db = get_db();
$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $brand = trim($_POST['brand'] ?? '');
    
    // Sanitize prices (support Persian numbers and remove commas)
    $rawPrice = to_english_num($_POST['price'] ?? '0');
    $rawPrice = preg_replace('/[^\d.]/', '', $rawPrice);
    $price = (float)$rawPrice;

    $rawDiscount = to_english_num($_POST['discount_price'] ?? '');
    $rawDiscount = preg_replace('/[^\d.]/', '', $rawDiscount);
    $discountPrice = ($rawDiscount !== '' && (float)$rawDiscount > 0) ? (float)$rawDiscount : null;

    $stock = max(0, (int)to_english_num($_POST['stock'] ?? '10'));
    $desc = $_POST['description'] ?? '';
    $highlights = trim($_POST['highlights'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;

    if (empty($name)) {
        $error = 'لطفاً نام محصول را وارد کنید.';
    } elseif ($price <= 0) {
        $error = 'لطفاً قیمت معتبر برای محصول وارد نمایید.';
    } else {
        try {
            // Generate Unique Slug
            $baseSlug = slugify($name);
            if (empty($baseSlug)) $baseSlug = 'product-' . time();
            $slug = $baseSlug;
            $counter = 1;
            while (true) {
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
                $checkStmt->execute([$slug]);
                if ($checkStmt->fetchColumn() == 0) break;
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Image Upload Handling
            $imagePath = 'assets/images/cat-gadgets.webp'; // Default fallback
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir(PATH_UPLOADS)) {
                    @mkdir(PATH_UPLOADS, 0755, true);
                }

                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

                if (in_array($ext, $allowedExts)) {
                    $newFilename = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $targetPath = PATH_UPLOADS . '/' . $newFilename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        $imagePath = 'uploads/' . $newFilename;
                    } else {
                        $error = 'آپلود تصویر ناموفق بود. پوشه uploads را بررسی نمایید.';
                    }
                } else {
                    $error = 'فرمت تصویر مجاز نیست. لطفاً JPG، PNG یا WebP آپلود کنید.';
                }
            }

            if (empty($error)) {
                // Calculate discount percent
                $discountPercent = 0;
                if ($discountPrice > 0 && $discountPrice < $price) {
                    $discountPercent = round((($price - $discountPrice) / $price) * 100);
                }

                // Default specifications JSON
                $specs = json_encode([
                    'ضمانت' => 'اصالت و سلامت فیزیکی کالا',
                    'زمان ارسال' => '۲۴ تا ۴۸ ساعت کاری'
                ], JSON_UNESCAPED_UNICODE);

                $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, brand, price, discount_price, discount_percent, stock, rating, reviews_count, description, highlights, specifications, is_featured, is_new, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 5.0, 1, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $catId, $name, $slug, $brand, $price, $discountPrice, $discountPercent, $stock, $desc, $highlights, $specs, $isFeatured, $isNew, $imagePath
                ]);

                redirect('products.php?published=1');
            }
        } catch (Exception $e) {
            $error = 'خطا در ثبت محصول در پایگاه داده: ' . $e->getMessage();
        }
    }
}
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>افزودن محصول جدید</h2>
    <a href="products.php" style="color: var(--admin-muted);">بازگشت به لیست محصولات</a>
</div>

<?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.2); color: var(--admin-red); padding: 14px; border-radius: 10px; margin-bottom: 20px; font-weight: bold;">
        <?php echo e($error); ?>
    </div>
<?php endif; ?>

<div class="admin-card-box" style="max-width: 800px;">
    <form method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label>نام محصول *</label>
            <input type="text" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" placeholder="نام کامل محصول..." class="form-control" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>دسته‌بندی *</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo e($c['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>برند</label>
                <input type="text" name="brand" value="<?php echo e($_POST['brand'] ?? ''); ?>" class="form-control" placeholder="مثال: Apple, Anker, Xiaomi">
            </div>
        </div>

        <div class="form-group">
            <label>مشخصات کوتاه / نکات برجسته بالای قیمت (جداشده با ویرگول)</label>
            <input type="text" name="highlights" value="<?php echo e($_POST['highlights'] ?? ''); ?>" class="form-control" placeholder="مثال: میکروفون HD, بلوتوث ۵.۲, باتری ۳۰ ساعت, حذف نویز ANC">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label>قیمت اصلی (تومان) *</label>
                <input type="text" name="price" value="<?php echo e($_POST['price'] ?? ''); ?>" placeholder="مثال: ۱۲۹۰۰۰۰" class="form-control" required>
            </div>

            <div class="form-group">
                <label>قیمت با تخفیف (تومان)</label>
                <input type="text" name="discount_price" value="<?php echo e($_POST['discount_price'] ?? ''); ?>" placeholder="اختیاری" class="form-control">
            </div>

            <div class="form-group">
                <label>موجودی انبار *</label>
                <input type="number" name="stock" value="<?php echo e($_POST['stock'] ?? '10'); ?>" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label>تصویر اصلی محصول (JPG, PNG, WebP) *</label>
            <input type="file" name="image" class="form-control image-upload-input" data-preview="prod-preview">
            <img id="prod-preview" style="max-width: 140px; margin-top: 10px; border-radius: 8px; border: 1px solid var(--admin-border); display: none;">
        </div>

        <div class="form-group">
            <label>توضیحات کامل محصول</label>
            <textarea name="description" class="form-control" rows="6" placeholder="توضیحات کامل محصول..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 24px;">
            <label style="cursor: pointer;"><input type="checkbox" name="is_featured" value="1" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>> محصول ویژه (نمایش در هوم‌پایج)</label>
            <label style="cursor: pointer;"><input type="checkbox" name="is_new" value="1" checked> محصول جدید</label>
        </div>

        <button type="submit" class="btn-green-add" style="padding: 14px 28px; font-size: 15px;">ذخیره و انتشار محصول</button>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
