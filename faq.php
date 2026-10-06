<?php
/**
 * FAQ Page (Dynamically Editable from Admin Panel)
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'faq';
$faqContent = get_setting('faq_content', '<h3>پرسش‌های متداول خریداران</h3><h4>۱. روند ثبت سفارش و پرداخت چگونه است؟</h4><p>کافیست محصول مورد نظر خود را به سبد خرید اضافه کنید، سپس با شماره همراه وارد شده و آدرس تحویل را تکمیل نمایید. پرداخت آنلاین از طریق درگاه امن زرین‌پال انجام می‌پذیرد.</p><h4>۲. زمان و نحوه ارسال سفارشات چقدر است؟</h4><p>ارسال پستی پیشتاز ظرف ۲ تا ۴ روز کاری به سراسر کشور تحویل می‌شود. برای استان قم ارسال با پیک شهری سریع نیز فعال می‌باشد.</p>');

$seoParams = ['title' => 'پرسش‌های متداول', 'description' => 'پاسخ به سوالات متداول مشتریان اطلس شاپ'];

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 800px; margin: 32px auto 60px auto;">
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>پرسش‌های متداول</span>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; line-height: 2; box-shadow: var(--shadow-sm); color: var(--text);">
        <h1 style="font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 20px;">پرسش‌های متداول</h1>
        <div style="font-size: 15px; line-height: 2;">
            <?php echo strip_tags($faqContent, '<p><br><strong><em><ul><ol><li><h2><h3><h4><table><tr><td><th><thead><tbody><span><div><img>'); ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
