<?php
$adminPage = 'content';
require_once __DIR__ . '/partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$db = get_db();

$stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) redirect('content.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $body = $_POST['body'];
    $seoTitle = trim($_POST['seo_title']);
    $metaDesc = trim($_POST['meta_description']);

    $image = $post['image'];
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

    $stmt = $db->prepare("UPDATE posts SET title = ?, excerpt = ?, body = ?, image = ?, seo_title = ?, meta_description = ? WHERE id = ?");
    $stmt->execute([$title, $excerpt, $body, $image, $seoTitle, $metaDesc, $id]);

    redirect('content.php');
}
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>ویرایش مقاله: <?php echo e($post['title']); ?></h2>
</div>

<div class="admin-card-box" style="max-width: 800px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>عنوان مقاله *</label>
            <input type="text" name="title" value="<?php echo e($post['title']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>خلاصه مقاله *</label>
            <textarea name="excerpt" class="form-control" rows="2" required><?php echo e($post['excerpt']); ?></textarea>
        </div>
        <div class="form-group">
            <label>تصویر شاخص مقاله</label>
            <input type="file" name="image" class="form-control">
            <img src="../<?php echo e($post['image']); ?>" style="max-width: 120px; margin-top: 8px; border-radius: 6px;">
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

            <textarea id="post-body-textarea" name="body" class="form-control" rows="10"><?php echo e($post['body']); ?></textarea>
        </div>

        <div class="form-group">
            <label>عنوان SEO</label>
            <input type="text" name="seo_title" value="<?php echo e($post['seo_title']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>توضیحات متای SEO</label>
            <textarea name="meta_description" class="form-control" rows="2"><?php echo e($post['meta_description']); ?></textarea>
        </div>
        <button type="submit" class="btn-green-add" style="padding: 12px 24px;">ذخیره تغییرات</button>
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
