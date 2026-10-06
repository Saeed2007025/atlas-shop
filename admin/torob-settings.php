<?php
/**
 * Admin Torob (ترب) Integration Settings
 */

$adminPage = 'torob';
require_once __DIR__ . '/partials/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('torob_active', isset($_POST['torob_active']) ? '1' : '0');
    set_setting('torob_token', trim($_POST['torob_token']));
    $message = 'تنظیمات اتصال به موتور جستجوی ترب با موفقیت ذخیره شد.';
}

$torobActive = get_setting('torob_active', '1');
$torobToken = get_setting('torob_token', bin2hex(random_bytes(8)));
$feedUrl = SITE_URL . '/api/torob.php' . (!empty($torobToken) ? '?token=' . $torobToken : '');
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>ماژول اختصاصی اتصال به موتور جستجوی ترب (Torob)</h2>
</div>

<?php if (!empty($message)): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Form -->
    <div class="admin-card-box">
        <h3>تنظیمات فید و API ترب</h3>
        <form method="POST" style="margin-top: 16px;">
            <div class="form-group" style="background: rgba(255, 255, 255, 0.03); padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                <label style="cursor: pointer; font-weight: bold; color: var(--admin-text);">
                    <input type="checkbox" name="torob_active" value="1" <?php echo $torobActive == '1' ? 'checked' : ''; ?>>
                    فعال‌سازی سرویس استعلام محصولات برای ربات ترب (Torob Crawler)
                </label>
            </div>

            <div class="form-group">
                <label>توکن امنیتی فید (Token / Security Key)</label>
                <input type="text" name="torob_token" value="<?php echo e($torobToken); ?>" class="form-control" style="direction: ltr;" required>
                <small style="color: var(--admin-muted); font-size: 11px; display: block; margin-top: 4px;">
                    این توکن جهت جلوگیری از سوءاستفاده غیرمجاز از API محصولات فروشگاه شما قرار داده شده است.
                </small>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label>لینک مستقیم فید محصولات جهت ثبت در پنل ترب (Product Feed URL):</label>
                <input type="text" value="<?php echo e($feedUrl); ?>" class="form-control" style="direction: ltr; font-weight: bold; color: var(--admin-accent);" readonly onclick="this.select();">
            </div>

            <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره تنظیمات ترب</button>
        </form>
    </div>

    <!-- Info Box -->
    <div class="admin-card-box">
        <h3>راهنمای اتصال به ترب</h3>
        <p style="font-size: 13px; color: var(--admin-muted); line-height: 1.8; margin-top: 12px;">
            موتور جستجوی ترب کالاها، قیمت‌ها و موجودی انبار فروشگاه شما را به صورت خودکار از طریق API فوق استعلام و به‌روزرسانی می‌کند.
        </p>
        <div style="margin-top: 20px;">
            <a href="<?php echo e($feedUrl); ?>" target="_blank" class="btn-green-add" style="display: inline-flex; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);">
                بررسی خروجی زنده API (JSON) ↗
            </a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
