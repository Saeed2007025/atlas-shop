<?php
/**
 * Categories Listing Page (صفحه کامل دسته‌بندی‌های محصولات)
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'categories';
$seoParams = [
    'title' => 'دسته‌بندی‌های محصولات',
    'description' => 'مشاهده تمام دسته‌بندی‌های کالاها شامل شارژر، کابل، ساعت هوشمند، هندزفری و پاوربانک.'
];

$db = get_db();
$categories = [];

if ($db) {
    try {
        $stmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.display_order ASC");
        $categories = $stmt->fetchAll();
    } catch (Exception $e) {}
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 24px; margin-bottom: 40px;">

    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>دسته‌بندی‌ها</span>
    </div>

    <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 20px; color: var(--text);">
        دسته‌بندی محصولات اطلس شاپ
    </h1>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;" class="categories-page-grid">
        <?php foreach ($categories as $cat): ?>
            <a href="shop?category=<?php echo e($cat['slug']); ?>" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--shadow-sm); transition: transform 0.2s, border-color 0.2s;">
                <div style="width: 64px; height: 64px; border-radius: var(--radius); background: var(--surface-soft); display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 6px; border: 1px solid var(--border);">
                    <img src="<?php echo e($cat['image'] ?? 'assets/images/cat-gadgets.webp'); ?>" alt="<?php echo e($cat['name']); ?>" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: bold; color: var(--text); margin-bottom: 4px;"><?php echo e($cat['name']); ?></h3>
                    <span style="font-size: 12px; color: var(--muted);"><?php echo to_persian_num($cat['product_count']); ?> کالا</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

</div>

<style>
@media (max-width: 768px) {
    .categories-page-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }
}
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
