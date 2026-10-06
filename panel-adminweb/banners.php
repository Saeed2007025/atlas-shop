<?php
/**
 * Admin Banners Management with Auto-Resizing & Smart Crop
 */

$adminPage = 'banners';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['add_banner'])) {
        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? 'shop');
        $position = trim($_POST['position'] ?? 'hero');
        $imagePath = 'assets/images/hero-1.webp';

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir(PATH_UPLOADS)) {
                @mkdir(PATH_UPLOADS, 0755, true);
            }

            // Determine target aspect ratio & dimensions
            $targetW = ($position === 'hero') ? 1200 : 600;
            $targetH = ($position === 'hero') ? 480 : 320;

            $newFilename = 'banner_' . $position . '_' . time() . '_' . rand(100, 999) . '.webp';
            $fullTargetPath = PATH_UPLOADS . '/' . $newFilename;

            // Auto-resize and crop to exact dimensions
            if (process_and_save_banner_image($_FILES['image']['tmp_name'], $fullTargetPath, $targetW, $targetH)) {
                $imagePath = 'uploads/' . $newFilename;
            } else {
                // Fallback move file
                $uploadCheck = validate_upload($_FILES['image']);
                if ($uploadCheck['valid']) {
                    $ext = $uploadCheck['ext'];
                    $fallbackFile = 'banner_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $fallbackFile)) {
                        $imagePath = 'uploads/' . $fallbackFile;
                    }
                } else {
                    $error = $uploadCheck['error'];
                }
            }
        }

        $stmt = $db->prepare("INSERT INTO banners (title, image, link, position, display_order, is_active, created_at) VALUES (?, ?, ?, ?, 0, 1, NOW())");
        $stmt->execute([$title, $imagePath, $link, $position]);
        redirect('banners.php?uploaded=1');
    }

    if (isset($_POST['delete_banner'])) {
        $delId = (int)$_POST['delete_id'];
        $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$delId]);
        redirect('banners.php');
    }
}

$banners = $db->query("SELECT * FROM banners ORDER BY position ASC, display_order ASC")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form -->
    <div class="admin-card-box">
        <h3>افزودن بنر جدید (تنظیم خودکار ابعاد)</h3>
        
        <?php if (isset($_GET['uploaded'])): ?>
            <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 10px; border-radius: 8px; margin: 12px 0; font-size: 13px;">
                ✔ بنر جدید با موفقیت برش، بهینه‌سازی و آپلود گردید.
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>عنوان بنر</label>
                <input type="text" name="title" placeholder="عنوان بنر..." class="form-control">
            </div>
            
            <div class="form-group">
                <label>موقعیت بنر *</label>
                <select name="position" class="form-control">
                    <option value="hero">اسلایدر اصلی هیرو (تنظیم خودکار به ۱۲۰۰ × ۴۸۰)</option>
                    <option value="promo">بنر تبلیغاتی دوتایی (تنظیم خودکار به ۶۰۰ × ۳۲۰)</option>
                </select>
            </div>

            <div class="form-group">
                <label>لینک مقصد کلیک</label>
                <input type="text" name="link" class="form-control" value="shop">
            </div>

            <div class="form-group">
                <label>تصویر بنر (با هر ابعادی) *</label>
                <input type="file" name="image" class="form-control image-upload-input" data-preview="banner-preview" required>
                <small style="color: var(--admin-muted); font-size: 11px; display: block; margin-top: 4px;">
                    نکته: هر تصویری با هر ابعادی آپلود کنید، سیستم به‌صورت ۱۰۰٪ خودکار آن را برش (Center Crop) داده و به ابعاد دقیق بنر استاندارد تبدیل می‌کند.
                </small>
                <img id="banner-preview" style="max-width: 100%; height: auto; margin-top: 10px; border-radius: 8px; border: 1px solid var(--admin-border); display: none;">
            </div>

            <button type="submit" name="add_banner" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 12px;">آپلود و تنظیم خودکار ابعاد بنر</button>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card-box">
        <h3>لیست بنرهای فعال</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>تصویر بنر</th>
                    <th>عنوان</th>
                    <th>موقعیت</th>
                    <th>لینک</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $b): ?>
                    <tr>
                        <td><img src="../<?php echo e($b['image']); ?>" style="width: 110px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);"></td>
                        <td style="font-weight: bold;"><?php echo e($b['title'] ?: 'بدون عنوان'); ?></td>
                        <td>
                            <?php if ($b['position'] === 'hero'): ?>
                                <span class="badge-status status-paid">هیرو (1200x480)</span>
                            <?php else: ?>
                                <span class="badge-status status-shipped">تبلیغاتی (600x320)</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo e($b['link']); ?></code></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $b['id']; ?>">
                                <button type="submit" name="delete_banner" value="1" onclick="return confirm('حذف این بنر؟')" style="background:none; border:none; color: var(--admin-red); font-weight: bold; cursor:pointer;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
