<?php
/**
 * Admin Banners Management
 */

$adminPage = 'banners';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $title = trim($_POST['title']);
    $link = trim($_POST['link']);
    $position = trim($_POST['position']);
    $image = 'assets/images/hero-1.webp';

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFilename = 'banner_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $newFilename)) {
            $image = 'uploads/' . $newFilename;
        }
    }

    $stmt = $db->prepare("INSERT INTO banners (title, image, link, position, display_order, is_active) VALUES (?, ?, ?, ?, 0, 1)");
    $stmt->execute([$title, $image, $link, $position]);
    redirect('banners.php');
}

if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$delId]);
    redirect('banners.php');
}

$banners = $db->query("SELECT * FROM banners ORDER BY position ASC, display_order ASC")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form -->
    <div class="admin-card-box">
        <h3>افزودن بنر جدید</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
            <div class="form-group">
                <label>عنوان بنر</label>
                <input type="text" name="title" class="form-control">
            </div>
            <div class="form-group">
                <label>موقعیت بنر</label>
                <select name="position" class="form-control">
                    <option value="hero">اسلایدر اصلی (Hero Banner - حداکثر ۳ بنر)</option>
                    <option value="promo">بنر تبلیغاتی دوتایی (Promo Banner)</option>
                </select>
            </div>
            <div class="form-group">
                <label>لینک مقصد</label>
                <input type="text" name="link" class="form-control" value="shop.php">
            </div>
            <div class="form-group">
                <label>تصویر بنر *</label>
                <input type="file" name="image" class="form-control" required>
            </div>
            <button type="submit" name="add_banner" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 10px;">ذخیره بنر</button>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card-box">
        <h3>لیست بنرها</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>تصویر</th>
                    <th>عنوان</th>
                    <th>موقعیت</th>
                    <th>لینک</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $b): ?>
                    <tr>
                        <td><img src="../<?php echo e($b['image']); ?>" style="width: 80px; height: 35px; object-fit: cover; border-radius: 4px;"></td>
                        <td><?php echo e($b['title']); ?></td>
                        <td><span class="badge-status status-paid"><?php echo $b['position'] === 'hero' ? 'هیرو اسلایدر' : 'تبلیغاتی دوتایی'; ?></span></td>
                        <td><code><?php echo e($b['link']); ?></code></td>
                        <td>
                            <a href="banners.php?delete=<?php echo $b['id']; ?>" onclick="return confirm('حذف بنر؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
