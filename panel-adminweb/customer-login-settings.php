<?php
/**
 * Admin SMS & Login Settings
 */

$adminPage = 'sms';
require_once __DIR__ . '/partials/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('sms_mock_mode', isset($_POST['sms_mock_mode']) ? '1' : '0');
    set_setting('sms_provider', trim($_POST['sms_provider']));
    set_setting('sms_api_key', trim($_POST['sms_api_key']));
    set_setting('sms_pattern', trim($_POST['sms_pattern']));
    set_setting('sms_sender_line', trim($_POST['sms_sender_line']));

    redirect('customer-login-settings.php?saved=1');
}

$mockMode = get_setting('sms_mock_mode', '1');
$provider = get_setting('sms_provider', 'mock');
$apiKey = get_setting('sms_api_key', '');
$pattern = get_setting('sms_pattern', '');
$sender = get_setting('sms_sender_line', '');
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>تنظیمات سامانه پیامک و ورود با OTP</h2>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        تنظیمات سامانه پیامکی با موفقیت ذخیره شد.
    </div>
<?php endif; ?>

<div class="admin-card-box" style="max-width: 600px;">
    <form method="POST">
        <div class="form-group" style="background: rgba(255, 255, 255, 0.03); padding: 14px; border-radius: 8px; margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--admin-text); font-weight: bold;">
                <input type="checkbox" name="sms_mock_mode" value="1" <?php echo $mockMode == '1' ? 'checked' : ''; ?>>
                فعال‌سازی حالت آزمایشی (Mock Mode) - کد تایید همواره ۱۲۳۴۵ خواهد بود
            </label>
        </div>

        <div class="form-group">
            <label>ارائه‌دهنده وب‌سرویس پیامک (Provider)</label>
            <select name="sms_provider" class="form-control">
                <option value="mock" <?php echo $provider === 'mock' ? 'selected' : ''; ?>>حالت تست خودکار (Mock)</option>
                <option value="kavenegar" <?php echo $provider === 'kavenegar' ? 'selected' : ''; ?>>کاوه نگار (Kavenegar)</option>
                <option value="ippanel" <?php echo $provider === 'ippanel' ? 'selected' : ''; ?>>آی‌پی پنل (IPPanel / FarazSMS)</option>
                <option value="melipayamak" <?php echo $provider === 'melipayamak' ? 'selected' : ''; ?>>ملی پیامک (Melipayamak)</option>
            </select>
        </div>

        <div class="form-group">
            <label>کلید اختصاصی API Key</label>
            <input type="text" name="sms_api_key" value="<?php echo e($apiKey); ?>" class="form-control" style="direction: ltr;">
        </div>

        <div class="form-group">
            <label>کد پترن / الگوی پیامک (Pattern Code)</label>
            <input type="text" name="sms_pattern" value="<?php echo e($pattern); ?>" class="form-control" style="direction: ltr;">
        </div>

        <div class="form-group">
            <label>خط اختصاصی ارسال‌کننده (Sender Line)</label>
            <input type="text" name="sms_sender_line" value="<?php echo e($sender); ?>" class="form-control" style="direction: ltr;">
        </div>

        <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره تنظیمات پیامک</button>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
