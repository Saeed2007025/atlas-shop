<?php
/**
 * Mobile OTP Login / Register Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'login';
$seoParams = [
    'title' => 'ورود / ثبت‌نام با شماره موبایل',
    'description' => 'ورود سریع به حساب کاربری اطلس شاپ با ارسال کد تایید پیامکی (OTP).'
];

$redirectUrl = $_GET['redirect'] ?? 'checkout';

if (preg_match('~^(http://|https://|//)~i', $redirectUrl) || strpos($redirectUrl, '://') !== false) {
    $redirectUrl = 'checkout';
}
$redirectUrl = ltrim($redirectUrl, '/');

if (is_customer_logged_in()) {
    redirect(SITE_URL . '/' . $redirectUrl);
}

$postMsg = '';
$postSuccess = false;

// Standard POST Fallback Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['do_send_otp'])) {
        $mobile = $_POST['mobile'] ?? '';
        $res = send_otp($mobile);
        $postMsg = $res['message'];
        $postSuccess = $res['success'];
    } elseif (isset($_POST['do_verify_otp'])) {
        $mobile = $_POST['mobile'] ?? '';
        $code = $_POST['code'] ?? '';
        $res = verify_otp($mobile, $code);
        if ($res['success']) {
            redirect(SITE_URL . '/' . $redirectUrl);
        } else {
            $postMsg = $res['message'];
        }
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 440px; margin: 50px auto 80px auto;">

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 36px 28px; box-shadow: var(--shadow-sm);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="font-size: 24px; font-weight: 800; color: var(--primary);">اطلس شاپ</div>
            <h1 style="font-size: 18px; margin-top: 8px; color: var(--text);">ورود یا ثبت‌نام</h1>
            <p style="font-size: 13px; color: var(--muted); margin-top: 4px;">لطفاً شماره همراه خود را وارد نمایید.</p>
        </div>

        <?php if (!empty($postMsg)): ?>
            <div style="background: <?php echo $postSuccess ? 'rgba(45,154,91,0.1)' : 'rgba(215,80,93,0.1)'; ?>; color: <?php echo $postSuccess ? 'var(--success)' : 'var(--danger)'; ?>; padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-size: 13px; text-align: center; font-weight: bold;">
                <?php echo e($postMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Mobile Input Form -->
        <form id="otp-mobile-form" method="POST" action="login">
            <?php echo csrf_field(); ?>
            <?php echo honeypot_field(); ?>

            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 8px; text-align: right;">شماره همراه</label>
                <input type="text" id="mobile-input" name="mobile" placeholder="۰۹۱۲۳۴۵۶۷۸۹" class="form-input" style="text-align: center; direction: ltr; font-weight: bold; font-size: 18px; letter-spacing: 2px; border-radius: 12px; padding: 12px 16px;" required>
            </div>

            <div id="mobile-error-msg" style="color: var(--danger); font-size: 13px; margin-bottom: 16px; text-align: center; display: none;"></div>

            <button type="submit" name="do_send_otp" value="1" id="send-otp-btn" class="action-btn btn-login" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; font-weight: bold; border-radius: 12px;">
                ارسال کد تایید پیامکی
            </button>
        </form>

        <!-- Step 2: OTP Verification Form -->
        <form id="otp-verify-form" method="POST" action="login" style="display: none; margin-top: 20px;">
            <?php echo csrf_field(); ?>
            <?php echo honeypot_field(); ?>

            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 8px; text-align: center;">کد تایید ۵ رقمی ارسال شده را وارد کنید</label>
                <input type="text" id="code-input" name="code" placeholder="کد تایید" maxlength="5" class="form-input" style="text-align: center; letter-spacing: 8px; font-weight: bold; font-size: 22px; border-radius: 12px; padding: 12px 16px;" required>
            </div>

            <div id="verify-info-msg" style="color: var(--success); font-size: 13px; margin-bottom: 16px; text-align: center; font-weight: bold;"></div>
            <div id="verify-error-msg" style="color: var(--danger); font-size: 13px; margin-bottom: 16px; text-align: center; display: none;"></div>

            <button type="submit" name="do_verify_otp" value="1" id="verify-otp-btn" class="action-btn btn-login" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; font-weight: bold; background: var(--success); border-radius: 12px;">
                تایید و ورود به حساب
            </button>

            <button type="button" id="change-mobile-btn" style="background: none; border: none; color: var(--muted); font-size: 13px; width: 100%; margin-top: 16px; cursor: pointer; text-decoration: underline;">
                ویرایش شماره همراه
            </button>
        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteBaseUrl = window.SITE_URL || '';
    const mobileForm = document.querySelector('#otp-mobile-form');
    const verifyForm = document.querySelector('#otp-verify-form');
    const mobileInput = document.querySelector('#mobile-input');
    const codeInput = document.querySelector('#code-input');
    const mobileError = document.querySelector('#mobile-error-msg');
    const verifyInfo = document.querySelector('#verify-info-msg');
    const verifyError = document.querySelector('#verify-error-msg');
    const changeMobileBtn = document.querySelector('#change-mobile-btn');

    let currentMobile = '';

    if (mobileForm && verifyForm) {
        mobileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            mobileError.style.display = 'none';
            currentMobile = mobileInput.value.trim();

            const formData = new FormData(mobileForm);
            formData.append('action', 'send_otp');

            fetch(`${siteBaseUrl}/api/auth.php`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mobileForm.style.display = 'none';
                    verifyForm.style.display = 'block';
                    verifyInfo.textContent = data.message;
                    if (data.code) codeInput.value = data.code; // Pre-fill mock code for test convenience
                } else {
                    mobileError.textContent = data.message;
                    mobileError.style.display = 'block';
                }
            })
            .catch(err => {
                // Submit standard form fallback
                mobileForm.submit();
            });
        });

        verifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            verifyError.style.display = 'none';

            const formData = new FormData(verifyForm);
            formData.append('action', 'verify_otp');
            formData.append('mobile', currentMobile);

            fetch(`${siteBaseUrl}/api/auth.php`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?php echo SITE_URL . "/" . e($redirectUrl); ?>';
                } else {
                    verifyError.textContent = data.message;
                    verifyError.style.display = 'block';
                }
            })
            .catch(err => {
                verifyForm.submit();
            });
        });

        if (changeMobileBtn) {
            changeMobileBtn.addEventListener('click', function() {
                verifyForm.style.display = 'none';
                mobileForm.style.display = 'block';
            });
        }
    }
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
