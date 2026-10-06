<?php
/**
 * Admin Customers & Newsletter Subscribers Management
 */

$adminPage = 'customers';
require_once __DIR__ . '/partials/header.php';

$db = get_db();
$activeTab = $_GET['tab'] ?? 'customers';

// Handle Newsletter Subscriber Delete
if (isset($_GET['del_subscriber'])) {
    $delId = (int)$_GET['del_subscriber'];
    $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$delId]);
    redirect('customers.php?tab=newsletter');
}

// Fetch Customers
$customers = $db->query("SELECT c.*, COUNT(o.id) AS total_orders, MAX(o.created_at) AS last_order_date FROM customers c LEFT JOIN orders o ON c.id = o.customer_id GROUP BY c.id ORDER BY c.id DESC")->fetchAll();

// Fetch Newsletter Subscribers
$db->exec("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$subscribers = $db->query("SELECT * FROM newsletter_subscribers ORDER BY id DESC")->fetchAll();
?>

<div class="card-title-bar" style="margin-bottom: 20px;">
    <h2>مدیریت مشتریان و اعضای خبرنامه</h2>
</div>

<!-- Tabs Bar -->
<div style="display: flex; gap: 12px; margin-bottom: 20px;">
    <a href="customers.php?tab=customers" class="btn-green-add" style="<?php echo $activeTab==='customers'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">
        <span>مشتریان ثبت‌نامی (<?php echo to_persian_num(count($customers)); ?>)</span>
    </a>
    <a href="customers.php?tab=newsletter" class="btn-green-add" style="<?php echo $activeTab==='newsletter'?'':'background:var(--admin-card); color:var(--admin-text); border:1px solid var(--admin-border);'; ?>">
        <span>اعضای خبرنامه (<?php echo to_persian_num(count($subscribers)); ?>)</span>
    </a>
</div>

<?php if ($activeTab === 'customers'): ?>
    <!-- Registered Customers Table -->
    <div class="admin-card-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره همراه</th>
                    <th>ایمیل</th>
                    <th>استان / شهر</th>
                    <th>تاریخ عضویت</th>
                    <th>تعداد سفارشات</th>
                    <th>آخرین سفارش</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="7" style="text-align:center; color:var(--admin-muted);">مشتری ثبت‌نامی وجود ندارد.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td style="font-weight: bold;"><?php echo e($c['full_name'] ?: 'کاربر جدید'); ?></td>
                            <td><code><?php echo e($c['mobile']); ?></code></td>
                            <td><?php echo e($c['email'] ?: '-'); ?></td>
                            <td><?php echo e($c['state']); ?> <?php echo $c['city'] ? '(' . e($c['city']) . ')' : ''; ?></td>
                            <td><?php echo to_persian_num(date('Y/m/d', strtotime($c['created_at']))); ?></td>
                            <td style="font-weight: bold; color: var(--admin-accent);"><?php echo to_persian_num($c['total_orders']); ?></td>
                            <td><?php echo $c['last_order_date'] ? to_persian_num(date('Y/m/d', strtotime($c['last_order_date']))) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <!-- Newsletter Subscribers Table -->
    <div class="admin-card-box">
        <h3>ایمیل‌های ثبت‌شده در خبرنامه</h3>
        <table class="admin-table" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>پست الکترونیک (ایمیل)</th>
                    <th>تاریخ و زمان عضویت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscribers)): ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--admin-muted);">هنوز ایمیلی در خبرنامه ثبت نشده است.</td></tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $idx => $sub): ?>
                        <tr>
                            <td><?php echo to_persian_num($idx + 1); ?></td>
                            <td style="font-weight: bold; color: var(--admin-accent);"><code><?php echo e($sub['email']); ?></code></td>
                            <td><?php echo to_persian_num(date('Y/m/d H:i', strtotime($sub['created_at']))); ?></td>
                            <td>
                                <a href="customers.php?tab=newsletter&del_subscriber=<?php echo $sub['id']; ?>" onclick="return confirm('آیا از حذف این ایمیل از لیست خبرنامه اطمینان دارید؟')" style="color: var(--admin-red); font-weight: bold;">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
