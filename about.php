<?php
/**
 * About Us Page (Dynamically Editable from Admin Panel)
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'about';
$siteName = get_setting('site_name', 'اطلس شاپ');
$aboutContent = get_setting('about_content', '<h3>درباره فروشگاه اینترنتی ' . e($siteName) . '</h3><p>فروشگاه اینترنتی «اطلس شاپ» مرجع تخصصی عرضه مستقیم جدیدترین و اورجینال‌ترین لوازم جانبی موبایل، گجت‌های هوشمند، شارژرهای اصلی و پاوربانک با ضمانت ۷ روزه بازگشت کالا و ضمانت اصالت در ایران می‌باشد.</p>');

$seoParams = ['title' => 'درباره ما', 'description' => 'درباره فروشگاه تخصصی لوازم جانبی اطلس شاپ'];

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 800px; margin: 32px auto 60px auto;">
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>درباره ما</span>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; line-height: 2; box-shadow: var(--shadow-sm); color: var(--text);">
        <h1 style="font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 16px;">درباره <?php echo e($siteName); ?></h1>
        <div style="font-size: 15px; line-height: 2;">
            <?php echo strip_tags($aboutContent, '<p><br><strong><em><ul><ol><li><h2><h3><h4><table><tr><td><th><thead><tbody><span><div><img>'); ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
