<?php
/**
 * Admin Categories Management
 */

$adminPage = 'categories';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $slug = slugify($name) ?: 'cat-' . time();
        $icon = trim($_POST['icon'] ?? 'grid');
        $image = 'assets/images/cat-gadgets.webp';

        if (!empty($_FILES['image']['name'])) {
            $uploadCheck = validate_upload($_FILES['image']);
            if ($uploadCheck['valid']) {
                $ext = $uploadCheck['ext'];
                $newFilename = 'cat_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $newFilename)) {
                    $image = 'uploads/' . $newFilename;
                }
            } else {
                $error = $uploadCheck['error'];
            }
        }

        $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, image, display_order, is_active) VALUES (?, ?, ?, ?, 0, 1)");
        $stmt->execute([$name, $slug, $icon, $image]);
        redirect('categories.php');
    }

    if (isset($_POST['delete_category'])) {
        $delId = (int)$_POST['delete_id'];
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
        redirect('categories.php');
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Add Category Form -->
    <div class="admin-card-box">
        <h3>افزودن دسته‌بندی جدید</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>عنوان دسته *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>آیکون SVG (کلاس/نام)</label>
                <input type="text" name="icon" class="form-control" value="grid">
            </div>
            <div class="form-group">
                <label>تصویر دسته‌بندی</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" name="add_category" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 10px;">ذخیره دسته‌بندی</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="admin-card-box">
        <h3>لیست دسته‌بندی‌ها</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>تصویر</th>
                    <th>نام دسته</th>
                    <th>اسلاگ (Slug)</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><img src="../<?php echo e($c['image']); ?>" style="width: 36px; height: 36px; object-fit: contain; border-radius: 6px; background: #FFF;"></td>
                        <td style="font-weight: bold;"><?php echo e($c['name']); ?></td>
                        <td><code><?php echo e($c['slug']); ?></code></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" name="delete_category" value="1" onclick="return confirm('حذف دسته؟')" style="background:none; border:none; color: var(--admin-red); font-weight: bold; cursor:pointer;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
