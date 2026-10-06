<?php
/**
 * Admin Product Reviews Management
 * Approval, Moderation & Delete
 */

$adminPage = 'reviews';
require_once __DIR__ . '/partials/header.php';

$db = get_db();

// Handle Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_id'])) {
        verify_csrf();
        $revId = (int)$_POST['approve_id'];
        $stmt = $db->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$revId]);

        // Recalculate Product Rating
        $stmt = $db->prepare("SELECT product_id FROM product_reviews WHERE id = ?");
        $stmt->execute([$revId]);
        $pId = $stmt->fetchColumn();

        if ($pId) {
            recalculate_product_rating($pId);
        }
        redirect('reviews.php?approved=1');
    }

    if (isset($_POST['delete_id'])) {
        verify_csrf();
        $revId = (int)$_POST['delete_id'];
        
        // Get product id before delete
        $stmt = $db->prepare("SELECT product_id FROM product_reviews WHERE id = ?");
        $stmt->execute([$revId]);
        $pId = $stmt->fetchColumn();

        $db->prepare("DELETE FROM product_reviews WHERE id = ?")->execute([$revId]);

        if ($pId) {
            recalculate_product_rating($pId);
        }
        redirect('reviews.php?deleted=1');
    }
}

function recalculate_product_rating($productId) {
    $db = get_db();
    $stmt = $db->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_count FROM product_reviews WHERE product_id = ? AND status = 'approved'");
    $stmt->execute([$productId]);
    $res = $stmt->fetch();

    $avg = $res['avg_rating'] ? round($res['avg_rating'], 1) : 5.0;
    $count = (int)($res['total_count'] ?? 0);

    $stmt = $db->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
    $stmt->execute([$avg, $count, $productId]);
}

$pendingReviews = $db->query("SELECT r.*, p.name AS product_name FROM product_reviews r LEFT JOIN products p ON r.product_id = p.id WHERE r.status = 'pending' ORDER BY r.id DESC")->fetchAll();
$approvedReviews = $db->query("SELECT r.*, p.name AS product_name FROM product_reviews r LEFT JOIN products p ON r.product_id = p.id WHERE r.status = 'approved' ORDER BY r.id DESC LIMIT 50")->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت نظرات و امتیازات مشتریان</h2>
</div>

<?php if (isset($_GET['approved'])): ?>
    <div style="background: rgba(16, 185, 129, 0.2); color: var(--admin-accent); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold;">
        ✔ دیدگاه با موفقیت تایید و در سایت منتشر گردید.
    </div>
<?php endif; ?>

<!-- Pending Reviews -->
<div class="admin-card-box" style="margin-bottom: 24px;">
    <h3>دیدگاه‌های در انتظار تایید مدیریت (<?php echo to_persian_num(count($pendingReviews)); ?>)</h3>
    <table class="admin-table" style="margin-top: 14px;">
        <thead>
            <tr>
                <th>محصول</th>
                <th>کاربر</th>
                <th>امتیاز</th>
                <th>متن دیدگاه</th>
                <th>تاریخ</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pendingReviews)): ?>
                <tr><td colspan="6" style="text-align:center; color:var(--admin-muted);">دیدگاه جدیدی در انتظار تایید وجود ندارد.</td></tr>
            <?php else: ?>
                <?php foreach ($pendingReviews as $pr): ?>
                    <tr>
                        <td style="font-weight: bold;"><?php echo e($pr['product_name'] ?: 'محصول #' . $pr['product_id']); ?></td>
                        <td><?php echo e($pr['user_name']); ?> <small style="color:var(--admin-muted);">(<?php echo e($pr['user_mobile']); ?>)</small></td>
                        <td style="color:#F59E0B; font-weight:bold;">★ <?php echo to_persian_num($pr['rating']); ?></td>
                        <td style="max-width:300px;"><?php echo e($pr['comment']); ?></td>
                        <td><?php echo to_persian_num(date('Y/m/d H:i', strtotime($pr['created_at']))); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="approve_id" value="<?php echo $pr['id']; ?>">
                                <button type="submit" class="btn-green-add" style="padding:4px 10px; font-size:12px; display:inline-flex; margin-left:8px; border:none; cursor:pointer;">تایید و انتشار</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $pr['id']; ?>">
                                <button type="submit" onclick="return confirm('حذف دیدگاه؟')" style="background:none; border:none; color:var(--admin-red); font-weight:bold; cursor:pointer; padding:0;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Approved Reviews -->
<div class="admin-card-box">
    <h3>آخرین دیدگاه‌های منتشرشده</h3>
    <table class="admin-table" style="margin-top: 14px;">
        <thead>
            <tr>
                <th>محصول</th>
                <th>کاربر</th>
                <th>امتیاز</th>
                <th>متن دیدگاه</th>
                <th>تاریخ</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($approvedReviews)): ?>
                <tr><td colspan="6" style="text-align:center; color:var(--admin-muted);">دیدگاهی تاکنون منتشر نشده است.</td></tr>
            <?php else: ?>
                <?php foreach ($approvedReviews as $ar): ?>
                    <tr>
                        <td style="font-weight: bold;"><?php echo e($ar['product_name']); ?></td>
                        <td><?php echo e($ar['user_name']); ?></td>
                        <td style="color:#F59E0B; font-weight:bold;">★ <?php echo to_persian_num($ar['rating']); ?></td>
                        <td style="max-width:300px;"><?php echo e($ar['comment']); ?></td>
                        <td><?php echo to_persian_num(date('Y/m/d', strtotime($ar['created_at']))); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?php echo $ar['id']; ?>">
                                <button type="submit" onclick="return confirm('حذف دیدگاه؟')" style="background:none; border:none; color:var(--admin-red); font-weight:bold; cursor:pointer; padding:0;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
