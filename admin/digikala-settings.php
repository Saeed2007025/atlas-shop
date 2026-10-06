<?php
/**
 * Admin Digikala (دیجی‌کالا) Integration Settings
 */

$adminPage = 'digikala';
require_once __DIR__ . '/partials/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('digikala_active', isset($_POST['digikala_active']) ? '1' : '0');
    set_setting('digikala_seller_id', trim($_POST['digikala_seller_id']));
    set_setting('digikala_api_key', trim($_POST['digikala_api_key']));
    $message = 'تنظیمات ماژول فروشندگان دیجی‌کالا با موفقیت ذخیره شد.';
}

$digikalaActive = get_setting('digikala_active', '1');
$digikalaSellerId = get_setting('digikala_seller_id', 'ATL-DK-SELLER-102');
$digikalaApiKey = get_setting('digikala_api_key', '');
$feedUrl = SITE_URL . '/api/digikala.php';
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>ماژول اختصاصی همگام‌سازی با مرکز فروشندگان دیجی‌کالا (Digikala Seller Center)</h2>
</div>

<?php if (!empty($message)): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Form -->
    <div class="admin-card-box">
        <h3>تنظیمات پنل فروشندگی دیجی‌کالا</h3>
        <form method="POST" style="margin-top: 16px;">
            <div class="form-group" style="background: rgba(255, 255, 255, 0.03); padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                <label style="cursor: pointer; font-weight: bold; color: var(--admin-text);">
                    <input type="checkbox" name="digikala_active" value="1" <?php echo $digikalaActive == '1' ? 'checked' : ''; ?>>
                    فعال‌سازی سرویس خروجی کالاها جهت همگام‌سازی با دیجی‌کالا
                </label>
            </div>

            <div class="form-group">
                <label>کد فروشندگی دیجی‌کالا (Seller ID / DKPC)</label>
                <input type="text" name="digikala_seller_id" value="<?php echo e($digikalaSellerId); ?>" class="form-control" style="direction: ltr;" required>
            </div>

            <div class="form-group">
                <label>کلید دسترسی Open API دیجی‌کالا</label>
                <input type="text" name="digikala_api_key" value="<?php echo e($digikalaApiKey); ?>" class="form-control" style="direction: ltr;">
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label>لینک مستقیم خروجی کالاها ویژه دیجی‌کالا (Rial JSON Catalog):</label>
                <input type="text" value="<?php echo e($feedUrl); ?>" class="form-control" style="direction: ltr; font-weight: bold; color: var(--admin-accent);" readonly onclick="this.select();">
            </div>

            <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره تنظیمات دیجی‌کالا</button>
        </form>
    </div>

    <!-- Info Box -->
    <div class="admin-card-box">
        <h3>راهنمای همگام‌سازی</h3>
        <p style="font-size: 13px; color: var(--admin-muted); line-height: 1.8; margin-top: 12px;">
            این ماژول موجودی کالاها و قیمت‌ها را به ریال برای سیستم همگام‌سازی قیمت خریداران دیجی‌کالا صادر کرده و کد SKU اختصاصی تولید می‌کند.
        </p>
        <div style="margin-top: 20px;">
            <a href="<?php echo e($feedUrl); ?>" target="_blank" class="btn-green-add" style="display: inline-flex; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);">
                مشاهده خروجی کاتالوگ دیجی‌کالا ↗
            </a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
