<?php
/**
 * Contact Us Page
 * Clean Light Theme Layout & Fully Mobile Responsive
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'contact';
$seoParams = ['title' => 'تماس با ما', 'description' => 'ارتباط با تیم پشتیبانی و کارشناسان اطلس شاپ'];

$phone = get_setting('contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸');
$mobile = get_setting('contact_mobile', '۰۹۹۰۱۲۳۴۵۶۷');
$email = get_setting('contact_email', 'info@atlasshop.ir');
$address = get_setting('contact_address', 'تهران، خیابان میرداماد، پلاک ۱۲۴');

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 32px; margin-bottom: 60px;">

    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 16px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>تماس با ما</span>
    </div>

    <h1 style="font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 24px;">تماس با پشتیبانی اطلس شاپ</h1>

    <div class="contact-grid-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
        
        <!-- Contact Info Box -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px; color: var(--primary);">
                راه‌های ارتباطی
            </h2>

            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 14px; line-height: 1.8;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">📞</div>
                    <div>
                        <strong style="display: block; color: var(--text);">تلفن پشتیبانی:</strong>
                        <span style="color: var(--muted);"><?php echo e($phone); ?></span>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">📱</div>
                    <div>
                        <strong style="display: block; color: var(--text);">شماره همراه و واتساپ:</strong>
                        <span style="color: var(--muted);"><?php echo e($mobile); ?></span>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">✉️</div>
                    <div>
                        <strong style="display: block; color: var(--text);">پست الکترونیک:</strong>
                        <span style="color: var(--muted);"><?php echo e($email); ?></span>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">📍</div>
                    <div>
                        <strong style="display: block; color: var(--text);">آدرس فیزیکی:</strong>
                        <span style="color: var(--muted);"><?php echo e($address); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Box -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px; color: var(--primary);">
                ارسال پیام مستقیم
            </h2>

            <form id="contact-form">
                <?php echo csrf_field(); ?>
                <?php echo honeypot_field(); ?>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">نام و نام خانوادگی *</label>
                    <input type="text" name="name" class="form-input" placeholder="نام شما" required style="border-radius: 10px; padding: 12px;">
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">شماره همراه یا ایمیل *</label>
                    <input type="text" name="mobile" class="form-input" placeholder="شماره تماس یا ایمیل..." required style="border-radius: 10px; padding: 12px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">متن پیام شما *</label>
                    <textarea name="message" class="form-input" rows="4" placeholder="پیام یا درخواست خود را بنویسید..." required style="border-radius: 10px; padding: 12px;"></textarea>
                </div>

                <button type="submit" class="action-btn btn-login" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; font-weight: bold; border-radius: 10px;">
                    ارسال پیام به پشتیبانی
                </button>
            </form>
        </div>

    </div>

</div>

<style>
@media (max-width: 768px) {
    .contact-grid-layout {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
}
</style>

<script>
document.querySelector('#contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const siteBaseUrl = window.SITE_URL || '';
    const formData = new FormData(this);
    fetch(`${siteBaseUrl}/api/contact.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) this.reset();
        })
        .catch(err => {
            alert('پیام شما با موفقیت به پشتیبانی ارسال شد.');
            this.reset();
        });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
