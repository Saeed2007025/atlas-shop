<?php
/**
 * Admin General Settings, Pages Editor & Zarinpal
 */

$adminPage = 'settings';
require_once __DIR__ . '/partials/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['save_settings'])) {
        set_setting('site_name', trim($_POST['site_name']));
        set_setting('site_title', trim($_POST['site_title']));
        set_setting('announcement_bar', trim($_POST['announcement_bar']));
        set_setting('contact_phone', trim($_POST['contact_phone']));
        set_setting('contact_mobile', trim($_POST['contact_mobile']));
        set_setting('contact_email', trim($_POST['contact_email']));
        set_setting('contact_address', trim($_POST['contact_address']));

        // Content for Pages
        set_setting('about_content', $_POST['about_content']);
        set_setting('faq_content', $_POST['faq_content']);

        // Logo Upload
        if (!empty($_FILES['site_logo']['name'])) {
            $uploadCheck = validate_upload($_FILES['site_logo']);
            if ($uploadCheck['valid']) {
                $ext = $uploadCheck['ext'];
                $newLogo = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], PATH_UPLOADS . '/' . $newLogo)) {
                    set_setting('site_logo', 'uploads/' . $newLogo);
                }
            } else {
                $error = $uploadCheck['error'];
            }
        }

        // Zarinpal
        set_setting('zarinpal_active', isset($_POST['zarinpal_active']) ? '1' : '0');
        set_setting('zarinpal_sandbox', isset($_POST['zarinpal_sandbox']) ? '1' : '0');
        set_setting('zarinpal_merchant_id', trim($_POST['zarinpal_merchant_id']));

        $message = 'تنظیمات با موفقیت ذخیره شد.';
    }

    if (isset($_POST['change_password'])) {
        $oldPass = trim($_POST['old_password']);
        $newPass = trim($_POST['new_password']);

        $db = get_db();
        $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$admin['id']]);
        $currentHash = $stmt->fetchColumn();

        if (password_verify($oldPass, $currentHash)) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([$newHash, $admin['id']]);
            $message = 'رمز عبور مدیریت با موفقیت تغییر یافت.';
        } else {
            $message = 'رمز عبور فعلی اشتباه وارد شده است.';
        }
    }
}

$siteName = get_setting('site_name', 'اطلس شاپ');
$siteTitle = get_setting('site_title', 'اطلس شاپ | فروشگاه تخصصی لوازم جانبی موبایل');
$siteLogo = get_setting('site_logo', 'assets/images/logo.svg');
$announcement = get_setting('announcement_bar', '');
$phone = get_setting('contact_phone', '');
$mobile = get_setting('contact_mobile', '');
$email = get_setting('contact_email', '');
$address = get_setting('contact_address', '');
$aboutContent = get_setting('about_content', '');
$faqContent = get_setting('faq_content', '');

$zarinpalActive = get_setting('zarinpal_active', '1');
$zarinpalSandbox = get_setting('zarinpal_sandbox', '1');
$zarinpalMerchant = get_setting('zarinpal_merchant_id', '');
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>تنظیمات عمومی، متن صفحات و درگاه پرداخت</h2>
</div>

<?php if (!empty($message)): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- General Settings Form -->
    <div class="admin-card-box">
        <h3>تنظیمات اصلی و هویت بصری</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>نام برند / فروشگاه</label>
                <input type="text" name="site_name" value="<?php echo e($siteName); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>لوگوی فروشگاه (SVG / PNG / JPG)</label>
                <input type="file" name="site_logo" class="form-control image-upload-input" data-preview="logo-preview">
                <div style="margin-top: 8px; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; display: inline-block;">
                    <img id="logo-preview" src="../<?php echo e($siteLogo); ?>" style="height: 38px; max-width: 200px; object-fit: contain;">
                </div>
            </div>

            <div class="form-group">
                <label>عنوان اصلی سایت (SEO Title)</label>
                <input type="text" name="site_title" value="<?php echo e($siteTitle); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>متن نوار اطلاع‌رسانی بالای هدر</label>
                <input type="text" name="announcement_bar" value="<?php echo e($announcement); ?>" class="form-control">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>تلفن ثابت پشتیبانی (نمایش در فاکتور و فوتر)</label>
                    <input type="text" name="contact_phone" value="<?php echo e($phone); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>شماره همراه پشتیبانی</label>
                    <input type="text" name="contact_mobile" value="<?php echo e($mobile); ?>" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>ایمیل پشتیبانی</label>
                <input type="email" name="contact_email" value="<?php echo e($email); ?>" class="form-control" style="direction: ltr;">
            </div>

            <div class="form-group">
                <label>آدرس فیزیکی فروشگاه</label>
                <textarea name="contact_address" class="form-control" rows="2"><?php echo e($address); ?></textarea>
            </div>

            <h3 style="margin: 24px 0 16px 0; border-top: 1px solid var(--admin-border); padding-top: 16px;">ویرایش محتوای صفحات درباره ما و سوالات متداول</h3>

            <div class="form-group">
                <label>محتوای صفحه «درباره ما» (HTML/Text)</label>
                <textarea name="about_content" class="form-control" rows="5"><?php echo e($aboutContent); ?></textarea>
            </div>

            <div class="form-group">
                <label>محتوای صفحه «پرسش‌های متداول» (HTML/Text)</label>
                <textarea name="faq_content" class="form-control" rows="5"><?php echo e($faqContent); ?></textarea>
            </div>

            <h3 style="margin: 24px 0 16px 0; border-top: 1px solid var(--admin-border); padding-top: 16px;">تنظیمات درگاه پرداخت زرین‌پال</h3>

            <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                <label style="cursor: pointer;"><input type="checkbox" name="zarinpal_active" value="1" <?php echo $zarinpalActive == '1' ? 'checked' : ''; ?>> فعال‌سازی درگاه زرین‌پال</label>
                <label style="cursor: pointer;"><input type="checkbox" name="zarinpal_sandbox" value="1" <?php echo $zarinpalSandbox == '1' ? 'checked' : ''; ?>> حالت آزمایشی (Sandbox)</label>
            </div>

            <div class="form-group">
                <label>مرچنت آیدی زرین‌پال (Merchant ID)</label>
                <input type="text" name="zarinpal_merchant_id" value="<?php echo e($zarinpalMerchant); ?>" class="form-control" style="direction: ltr;">
            </div>

            <button type="submit" name="save_settings" value="1" class="btn-green-add" style="padding: 12px 24px;">ذخیره کلیه تنظیمات</button>
        </form>
    </div>

    <!-- Admin Password Change -->
    <div class="admin-card-box">
        <h3>تغییر رمز عبور مدیریت</h3>
        <form method="POST" style="margin-top: 16px;">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>رمز عبور فعلی *</label>
                <input type="password" name="old_password" class="form-control" required style="direction: ltr;">
            </div>
            <div class="form-group">
                <label>رمز عبور جدید *</label>
                <input type="password" name="new_password" class="form-control" required style="direction: ltr;">
            </div>
            <button type="submit" name="change_password" value="1" class="btn-green-add" style="width: 100%; justify-content: center; padding: 10px;">تغییر رمز عبور</button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
