<?php
/**
 * Admin Content Management (Blog Posts)
 */

$adminPage = 'content';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$delId]);
    redirect('content.php');
}

$posts = $db->query("SELECT * FROM posts ORDER BY id DESC")->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت محتوا (وبلاگ)</h2>
    <a href="post-create.php" class="btn-green-add">+ افزودن مقاله جدید</a>
</div>

<div class="admin-card-box">
    <table class="admin-table">
        <thead>
            <tr>
                <th>تصویر</th>
                <th>عنوان مقاله</th>
                <th>نویسنده</th>
                <th>بازدید</th>
                <th>تاریخ انتشار</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><img src="../<?php echo e($p['image']); ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;"></td>
                    <td style="font-weight: bold;"><?php echo e($p['title']); ?></td>
                    <td><?php echo e($p['author']); ?></td>
                    <td><?php echo to_persian_num($p['views']); ?></td>
                    <td><?php echo to_persian_num(date('Y/m/d', strtotime($p['created_at']))); ?></td>
                    <td>
                        <a href="post-edit.php?id=<?php echo $p['id']; ?>" style="color: var(--admin-blue); margin-left: 12px; font-weight: bold;">ویرایش</a>
                        <a href="content.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('حذف مقاله؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
