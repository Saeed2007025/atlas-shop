<?php
/**
 * Single Blog Post Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$slug = trim($_GET['slug'] ?? '');
$db = get_db();
$post = null;

if ($slug && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if ($post) {
            $db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);
        }
    } catch (Exception $e) {
        $post = null;
    }
}

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/inc/header.php';
    echo '<div class="container" style="padding: 60px 20px; text-align: center;"><h1>مقاله مورد نظر یافت نشد.</h1><p><a href="blog.php">بازگشت به وبلاگ</a></p></div>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

$activePage = 'blog';
$seoParams = [
    'title' => $post['seo_title'] ?: $post['title'],
    'description' => $post['meta_description'] ?: $post['excerpt'],
    'type' => 'article',
    'post' => $post
];

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="max-width: 800px; margin: 24px auto;">

    <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <a href="blog.php">وبلاگ</a> &gt; <span><?php echo e($post['title']); ?></span>
    </div>

    <article style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
        <h1 style="font-size: 26px; font-weight: 800; margin-bottom: 12px; line-height: 1.4;"><?php echo e($post['title']); ?></h1>

        <div style="font-size: 13px; color: var(--muted); margin-bottom: 24px; display: flex; gap: 16px;">
            <span>نویسنده: <?php echo e($post['author']); ?></span>
            <span>تاریخ انتشار: <?php echo to_persian_num(date('Y/m/d', strtotime($post['created_at']))); ?></span>
            <span>بازدید: <?php echo to_persian_num($post['views']); ?></span>
        </div>

        <img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" style="width: 100%; border-radius: var(--radius); margin-bottom: 24px;">

        <div style="line-height: 2; font-size: 15px; color: var(--text);">
            <?php echo strip_tags($post['body'], '<p><br><strong><em><ul><ol><li><h2><h3><h4><table><tr><td><th><thead><tbody><span><div><img>'); ?>
        </div>
    </article>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
