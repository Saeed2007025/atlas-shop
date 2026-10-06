<?php
$adminPage = 'content';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $body = $_POST['body'];
    $seoTitle = trim($_POST['seo_title']);
    $metaDesc = trim($_POST['meta_description']);
    $slug = slugify($title) ?: 'post-' . time();
    $image = 'assets/images/hero-1.webp';

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFilename = 'post_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $newFilename)) {
            $image = 'uploads/' . $newFilename;
        }
    }

    $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, body, image, seo_title, meta_description, author, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'تیم تحریریه اطلس شاپ', 'published', NOW())");
    $stmt->execute([$title, $slug, $excerpt, $body, $image, $seoTitle, $metaDesc]);

    redirect('content.php');
}
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>افزودن مقاله سئو شده جدید</h2>
</div>

<div class="admin-card-box" style="max-width: 800px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>عنوان مقاله *</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>خلاصه مقاله (Excerpt) *</label>
            <textarea name="excerpt" class="form-control" rows="2" required></textarea>
        </div>
        <div class="form-group">
            <label>تصویر شاخص مقاله</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="form-group">
            <label>محتوای کامل مقاله (HTML)</label>
            <textarea name="body" class="form-control" rows="8"></textarea>
        </div>
        <div class="form-group">
            <label>عنوان SEO (Title)</label>
            <input type="text" name="seo_title" class="form-control">
        </div>
        <div class="form-group">
            <label>توضیحات متای SEO (Meta Description)</label>
            <textarea name="meta_description" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره و انتشار مقاله</button>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
