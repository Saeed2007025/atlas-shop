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

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadCheck = validate_upload($_FILES['image']);
        if ($uploadCheck['valid']) {
            $ext = $uploadCheck['ext'];
            $newFilename = 'post_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], PATH_UPLOADS . '/' . $newFilename)) {
                $image = 'uploads/' . $newFilename;
            }
        } else {
            $error = $uploadCheck['error'];
        }
    }

    $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, body, image, seo_title, meta_description, author, status, views, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'تیم تحریریه اطلس شاپ', 'published', 1, NOW())");
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
            <label>محتوای کامل مقاله (HTML / Rich Text)</label>

            <!-- Formatting Toolbar Buttons -->
            <div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                <button type="button" onclick="insertTag('<b>', '</b>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);"><b>B (بولد)</b></button>
                <button type="button" onclick="insertTag('<i>', '</i>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);"><i>I (ایمالیک)</i></button>
                <button type="button" onclick="insertTag('<h3>', '</h3>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);">H3 (تیتر)</button>
                <button type="button" onclick="insertTag('<a href=\'shop?category=chargers\'>', '</a>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);">🔗 ایجاد لینک داخلی</button>
                <button type="button" onclick="insertTag('<ul><li>', '</li></ul>')" class="btn-green-add" style="padding: 4px 10px; font-size: 12px; background: var(--admin-card); color: var(--admin-text); border: 1px solid var(--admin-border);">• لیست گلوله‌ای</button>
            </div>

            <textarea id="post-body-textarea" name="body" class="form-control" rows="10"></textarea>
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

<script>
function insertTag(openTag, closeTag) {
    const txt = document.getElementById('post-body-textarea');
    const start = txt.selectionStart;
    const end = txt.selectionEnd;
    const selectedText = txt.value.substring(start, end) || 'متن نمونه';
    const replacement = openTag + selectedText + closeTag;
    txt.value = txt.value.substring(0, start) + replacement + txt.value.substring(end);
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
