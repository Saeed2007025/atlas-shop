<?php
/**
 * Atlas Shop Store Catalog Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'shop';
$seoParams = [
    'title' => 'فروشگاه و لیست محصولات',
    'description' => 'مشاهده و خرید انواع گجت‌های هوشمند، هندزفری، شارژر، کابل و پاوربانک.'
];

$db = get_db();

// Filter parameters from GET query string
$catSlug = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$categories = [];
$products = [];
$totalProducts = 0;
$totalPages = 1;

if ($db) {
    try {
        // Fetch Categories for Sidebar
        $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

        // Build SQL Query
        $where = ["1=1"];
        $params = [];

        if ($catSlug) {
            $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$catSlug]);
            $catId = $stmt->fetchColumn();
            if ($catId) {
                $where[] = "category_id = ?";
                $params[] = $catId;
            }
        }

        if ($search) {
            $where[] = "(name LIKE ? OR brand LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(" AND ", $where);

        // Order By
        $orderBy = "id DESC";
        if ($sort === 'cheapest') $orderBy = "price ASC";
        if ($sort === 'expensive') $orderBy = "price DESC";
        if ($sort === 'rating') $orderBy = "rating DESC";
        if ($sort === 'best_selling') $orderBy = "reviews_count DESC";

        // Total Count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE $whereClause");
        $countStmt->execute($params);
        $totalProducts = $countStmt->fetchColumn();
        $totalPages = ceil($totalProducts / $limit);

        // Fetch Page Items
        $sql = "SELECT * FROM products WHERE $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

    } catch (Exception $e) {}
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 24px;">

    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>فروشگاه</span>
    </div>

    <div class="shop-main-layout" style="display: grid; grid-template-columns: 260px 1fr; gap: 24px; margin-bottom: 40px;">

        <!-- Sidebar Categories (Desktop Only - Hidden on Mobile) -->
        <aside class="shop-sidebar-filters" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; height: fit-content; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary);">
                دسته‌بندی محصولات
            </h3>

            <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;">
                <li>
                    <a href="shop" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: var(--radius-sm); font-size: 14px; font-weight: bold; background: <?php echo empty($catSlug) ? 'var(--primary-soft)' : 'transparent'; ?>; color: <?php echo empty($catSlug) ? 'var(--primary)' : 'var(--text)'; ?>;">
                        <span>همه محصولات</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                </li>
                <?php foreach ($categories as $c): ?>
                    <?php $isActiveCat = ($catSlug === $c['slug']); ?>
                    <li>
                        <a href="shop?category=<?php echo e($c['slug']); ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: var(--radius-sm); font-size: 14px; font-weight: <?php echo $isActiveCat ? 'bold' : 'normal'; ?>; background: <?php echo $isActiveCat ? 'var(--primary-soft)' : 'transparent'; ?>; color: <?php echo $isActiveCat ? 'var(--primary)' : 'var(--text)'; ?>;">
                            <span><?php echo e($c['name']); ?></span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Main Product Grid & Header -->
        <main>
            <!-- Sort & Results Count Bar -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                <div style="font-size: 14px; font-weight: 500;">
                    نمایش <strong><?php echo to_persian_num(count($products)); ?></strong> کالا از <strong><?php echo to_persian_num($totalProducts); ?></strong>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-size: 13px; color: var(--muted);">مرتب‌سازی:</label>
                    <select class="form-select" style="padding: 6px 12px;" onchange="location = this.value;">
                        <?php
                            $queryParams = $_GET;
                            function build_shop_url($sortKey) {
                                global $queryParams;
                                $qp = $queryParams;
                                $qp['sort'] = $sortKey;
                                return e('shop?' . http_build_query($qp));
                            }
                        ?>
                        <option value="<?php echo build_shop_url('newest'); ?>" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                        <option value="<?php echo build_shop_url('cheapest'); ?>" <?php echo $sort === 'cheapest' ? 'selected' : ''; ?>>ارزان‌ترین</option>
                        <option value="<?php echo build_shop_url('expensive'); ?>" <?php echo $sort === 'expensive' ? 'selected' : ''; ?>>گران‌ترین</option>
                        <option value="<?php echo build_shop_url('rating'); ?>" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>بیشترین امتیاز</option>
                        <option value="<?php echo build_shop_url('best_selling'); ?>" <?php echo $sort === 'best_selling' ? 'selected' : ''; ?>>پرفروش‌ترین</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 60px 20px; text-align: center;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--muted); margin-bottom: 12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <h3>محصولی با مشخصات انتخاب شده یافت نشد!</h3>
                    <p style="color: var(--muted); margin-top: 8px;">لطفاً دسته‌بندی دیگری را انتخاب کنید.</p>
                </div>
            <?php else: ?>
                <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <?php foreach ($products as $p): ?>
                        <?php $effectivePrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price']; ?>
                        <div class="product-card">
                            <?php if ($p['discount_percent'] > 0): ?>
                                <span class="product-badge"><?php echo to_persian_num($p['discount_percent']); ?>٪ تخفیف</span>
                            <?php endif; ?>

                            <a href="product?id=<?php echo $p['id']; ?>" class="product-card-link">
                                <div class="product-image-wrap">
                                    <img src="<?php echo e($p['image']); ?>" alt="<?php echo e($p['name']); ?>" loading="lazy">
                                </div>
                                <div class="product-brand"><?php echo e($p['brand']); ?></div>
                                <div class="product-title"><?php echo e($p['name']); ?></div>
                                <div class="product-rating">
                                    ★ <span><?php echo to_persian_num($p['rating']); ?></span> (<?php echo to_persian_num($p['reviews_count']); ?> نظر)
                                </div>
                                <div class="product-price-box">
                                    <?php if ($p['discount_price'] > 0): ?>
                                        <span class="product-old-price"><?php echo format_price($p['price'], ''); ?></span>
                                    <?php endif; ?>
                                    <span class="product-current-price"><?php echo format_price($effectivePrice); ?></span>
                                </div>
                            </a>

                            <div class="product-card-footer">
                                <span style="font-size:12px; color:var(--muted);"><?php echo $p['stock'] > 0 ? 'موجود در انبار' : 'ناموجود'; ?></span>
                                <button type="button" onclick="addToCart(<?php echo $p['id']; ?>)" class="btn-add-cart-icon" title="افزودن به سبد خرید">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 32px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php
                                $qp = $_GET;
                                $qp['page'] = $i;
                                $pageUrl = e('shop?' . http_build_query($qp));
                            ?>
                            <a href="<?php echo $pageUrl; ?>" style="width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); font-weight: bold; background: <?php echo $page == $i ? 'var(--primary)' : 'var(--surface)'; ?>; color: <?php echo $page == $i ? '#FFFFFF' : 'var(--text)'; ?>;">
                                <?php echo to_persian_num($i); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </main>

    </div>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
