<?php
/**
 * Blog Posts Listing Page
 * Cards styled wide and compact matching Homepage
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'blog';
$seoParams = [
    'title' => 'وبلاگ و راهنمای خرید گجت',
    'description' => 'مقالات تخصصی، اخبار دنیای لوازم جانبی موبایل و راهنمای خرید پاوربانک، شارژر و هندزفری.'
];

$db = get_db();
$posts = [];
if ($db) {
    try {
        $posts = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY id DESC")->fetchAll();
    } catch (Exception $e) {
        $posts = [];
    }
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 24px; margin-bottom: 40px;">

    <div style="font-size: 13px; color: var(--muted); margin-bottom: 8px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>وبلاگ</span>
    </div>

    <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 24px; color: var(--text);">راهنمای خرید و مقالات آموزشی</h1>

    <?php if (empty($posts)): ?>
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 60px 20px; text-align: center;">
            <p style="color: var(--muted);">در حال حاضر مقاله‌ای منتشر نشده است.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;" class="blog-grid">
            <?php foreach ($posts as $p): ?>
                <a href="post.php?slug=<?php echo e($p['slug']); ?>" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                    <img src="<?php echo e($p['image']); ?>" alt="<?php echo e($p['title']); ?>" style="width: 100%; aspect-ratio: 16/9; object-fit: cover;">
                    <div style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
                        <div style="font-size: 12px; color: var(--muted); margin-bottom: 6px;"><?php echo e($p['author']); ?> • <?php echo to_persian_num(date('Y/m/d', strtotime($p['created_at']))); ?></div>
                        <h2 style="font-size: 15px; font-weight: bold; margin-bottom: 8px; line-height: 1.4; color: var(--text);"><?php echo e($p['title']); ?></h2>
                        <p style="font-size: 13px; color: var(--muted); line-height: 1.5; height: 38px; overflow: hidden;"><?php echo e($p['excerpt']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<style>
@media (max-width: 768px) {
    .blog-grid {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
}
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
