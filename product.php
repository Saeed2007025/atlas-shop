<?php
/**
 * Single Product Detail Page
 * Clean Light Palette matching Homepage (#2F67E8 Blue + White)
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$productId = (int)($_GET['id'] ?? 0);
$db = get_db();
$product = null;
$category = null;
$variants = [];
$relatedProducts = [];
$reviews = [];

// Handle Review Submission
$reviewMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    verify_csrf();
    check_honeypot();

    $revName = trim($_POST['user_name'] ?? '');
    $revMobile = trim($_POST['user_mobile'] ?? '');
    $revRating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $revComment = trim($_POST['comment'] ?? '');

    if (!empty($revName) && !empty($revComment)) {
        $stmt = $db->prepare("INSERT INTO product_reviews (product_id, user_name, user_mobile, rating, comment, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$productId, $revName, $revMobile, $revRating, $revComment]);

        $reviewMsg = 'دیدگاه شما با موفقیت ثبت گردید و پس از بررسی و تایید مدیریت منتشر خواهد شد.';
    } else {
        $reviewMsg = 'لطفاً نام و متن نظر خود را وارد کنید.';
    }
}

if ($productId && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            // Category
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$product['category_id']]);
            $category = $stmt->fetch();

            // Variants
            $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ?");
            $stmt->execute([$productId]);
            $variants = $stmt->fetchAll();

            $stmt = $db->prepare("SELECT * FROM product_reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC");
            $stmt->execute([$productId]);
            $reviews = $stmt->fetchAll();

            // Related Products
            $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 5");
            $stmt->execute([$product['category_id'], $productId]);
            $relatedProducts = $stmt->fetchAll();
        }
    } catch (Exception $e) {}
}

if (!$product) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/inc/header.php';
    echo '<div class="container" style="padding: 60px 20px; text-align: center;"><h1>محصول مورد نظر یافت نشد.</h1><p><a href="shop" class="action-btn btn-login" style="display:inline-flex;">بازگشت به فروشگاه</a></p></div>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

$seoParams = [
    'title' => $product['name'],
    'description' => mb_substr(strip_tags($product['description']), 0, 160, 'UTF-8'),
    'type' => 'product',
    'product' => $product
];

$effectivePrice = ($product['discount_price'] > 0) ? $product['discount_price'] : $product['price'];
$specs = json_decode($product['specifications'] ?? '{}', true) ?: [];

// Parse Highlights / Short Specs
$highlights = [];
if (!empty($product['highlights'])) {
    $highlights = array_filter(array_map('trim', explode(',', $product['highlights'])));
}

$activePage = 'shop';
$isDarkTheme = false; // Clean light theme matching homepage palette!
require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 24px;">

    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt;
        <a href="shop">فروشگاه</a> &gt;
        <?php if ($category): ?>
            <a href="shop?category=<?php echo e($category['slug']); ?>"><?php echo e($category['name']); ?></a> &gt;
        <?php endif; ?>
        <span><?php echo e($product['name']); ?></span>
    </div>

    <!-- Main Product Detail Container -->
    <div class="product-detail-layout" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; display: grid; grid-template-columns: 1fr 1.2fr; gap: 32px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">

        <!-- Image Gallery -->
        <div>
            <div style="position: relative; border-radius: var(--radius); overflow: hidden; background: #FFFFFF; border: 1px solid var(--border); aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <?php if ($product['discount_percent'] > 0): ?>
                    <span class="product-badge" style="top:16px; right:16px; font-size:13px; padding:4px 12px; background:var(--danger);"><?php echo to_persian_num($product['discount_percent']); ?>٪ تخفیف</span>
                <?php endif; ?>
                <img id="main-product-img" src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" style="max-width: 90%; max-height: 90%; object-fit: contain;">
            </div>

            <!-- Thumbnail Strip -->
            <div style="display: flex; gap: 12px;">
                <div onclick="document.querySelector('#main-product-img').src='<?php echo e($product['image']); ?>'" style="width: 70px; height: 70px; border-radius: var(--radius-sm); border: 2px solid var(--primary); padding: 4px; cursor: pointer; background:#FFFFFF;">
                    <img src="<?php echo e($product['image']); ?>" style="width:100%; height:100%; object-fit:contain;">
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div>
            <div style="font-size: 13px; color: var(--primary); font-weight: bold; margin-bottom: 6px; text-transform: uppercase;">
                <?php echo e($product['brand']); ?>
            </div>

            <h1 style="font-size: 20px; font-weight: 800; margin-bottom: 10px; line-height: 1.4; color: var(--text);">
                <?php echo e($product['name']); ?>
            </h1>

            <div style="display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); margin-bottom: 20px;">
                <span style="color: #F59E0B; font-weight: bold;">★ <?php echo to_persian_num($product['rating']); ?></span>
                <span>(<?php echo to_persian_num(count($reviews) ?: $product['reviews_count']); ?> نظر ثبت شده)</span>
                <span>•</span>
                <span style="color: var(--success); font-weight: bold;"><?php echo $product['stock'] > 0 ? 'موجود در انبار' : 'ناموجود'; ?></span>
            </div>

            <!-- Key Highlights (مشخصات کوتاه بالای قیمت) -->
            <?php if (!empty($highlights)): ?>
                <div style="display: grid; grid-template-columns: repeat(<?php echo min(4, count($highlights)); ?>, 1fr); gap: 10px; background: var(--surface-soft); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; text-align: center; font-size: 12px; border: 1px solid var(--border);">
                    <?php foreach ($highlights as $h): ?>
                        <div>
                            <div style="font-weight: bold; color: var(--text);"><?php echo e($h); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Default Highlights fallback -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: var(--surface-soft); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; text-align: center; font-size: 12px; border: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: bold; color: var(--text);">ارسال سریع</div>
                        <div style="color: var(--muted); font-size: 10px;">سراسر کشور</div>
                    </div>
                    <div>
                        <div style="font-weight: bold; color: var(--text);">ضمانت اصالت</div>
                        <div style="color: var(--muted); font-size: 10px;">۱۰۰٪ اصلی</div>
                    </div>
                    <div>
                        <div style="font-weight: bold; color: var(--text);">مهلت تست</div>
                        <div style="color: var(--muted); font-size: 10px;">۷ روز بازگشت</div>
                    </div>
                    <div>
                        <div style="font-weight: bold; color: var(--text);">پشتیبانی</div>
                        <div style="color: var(--muted); font-size: 10px;">۲۴/۷ فعال</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Variants Selection -->
            <?php if (!empty($variants)): ?>
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 8px; color: var(--text);">انتخاب رنگ / مدل:</label>
                    <div style="display: flex; gap: 10px;">
                        <?php foreach ($variants as $v): ?>
                            <span style="padding: 6px 16px; border: 1px solid var(--primary); border-radius: 20px; font-size: 13px; cursor: pointer; background: var(--primary-soft); color: var(--primary); font-weight: bold;">
                                <?php echo e($v['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Price Box -->
            <div style="margin-bottom: 24px; background: var(--surface-soft); padding: 16px; border-radius: var(--radius); border: 1px solid var(--border);">
                <div style="display: flex; align-items: baseline; gap: 12px;">
                    <span style="font-size: 24px; font-weight: 800; color: var(--primary);"><?php echo format_price($effectivePrice); ?></span>
                    <?php if ($product['discount_price'] > 0): ?>
                        <span style="font-size: 13px; color: var(--muted); text-decoration: line-through;"><?php echo format_price($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 12px; color: var(--success); margin-top: 6px; font-weight: 500;">
                    ✔ گارانتی اصالت و سلامت فیزیکی کالا | ارسال سریع ۲۴ تا ۴۸ ساعته
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="display: flex; align-items: center; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--surface-soft);">
                    <button type="button" onclick="let q=document.querySelector('#prod-qty'); q.value=Math.max(1, parseInt(q.value)-1);" style="padding: 10px 14px; background: none; border: none; cursor: pointer; font-weight: bold; color: var(--text);">-</button>
                    <input type="number" id="prod-qty" value="1" min="1" style="width: 36px; text-align: center; border: none; background: none; color: var(--text); font-weight: bold;" readonly>
                    <button type="button" onclick="let q=document.querySelector('#prod-qty'); q.value=parseInt(q.value)+1;" style="padding: 10px 14px; background: none; border: none; cursor: pointer; font-weight: bold; color: var(--text);">+</button>
                </div>

                <button type="button" onclick="addToCart(<?php echo $product['id']; ?>, parseInt(document.querySelector('#prod-qty').value))" class="action-btn btn-login" style="padding: 12px 24px; font-size: 15px; font-weight: bold; flex: 1; justify-content: center; background: var(--primary); color: #FFFFFF;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>افزودن به سبد خرید</span>
                </button>
            </div>

        </div>

    </div>

    <!-- Description & Technical Specs -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary);">
            توضیحات کامل محصول
        </h2>
        <div style="line-height: 2; color: var(--text); margin-bottom: 32px; font-size: 15px;">
            <?php echo strip_tags($product['description'], '<p><br><strong><em><ul><ol><li><h2><h3><h4><table><tr><td><th><thead><tbody><span><div><img>'); ?>
        </div>

        <?php if (!empty($specs)): ?>
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary);">
                مشخصات فنی
            </h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <?php foreach ($specs as $key => $val): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px; width: 180px; font-weight: bold; color: var(--muted); background: var(--surface-soft);"><?php echo e($key); ?></td>
                            <td style="padding: 12px; color: var(--text);"><?php echo e($val); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Customer Reviews Section (نظرات و امتیازات خریداران) -->
    <div id="reviews-section" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary); display: flex; align-items: center; justify-content: space-between;">
            <span>نظرات و امتیازات خریداران (<?php echo to_persian_num(count($reviews)); ?>)</span>
            <span style="font-size: 14px; color: #F59E0B;">امتیاز کلی: ★ <?php echo to_persian_num($product['rating']); ?> از ۵</span>
        </h2>

        <?php if (!empty($reviewMsg)): ?>
            <div style="background: rgba(45, 154, 91, 0.1); color: var(--success); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-weight: bold; font-size: 13px;">
                <?php echo e($reviewMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Approved Reviews List -->
        <?php if (empty($reviews)): ?>
            <p style="color: var(--muted); font-size: 14px; margin-bottom: 24px;">هنوز نظری برای این محصول ثبت نشده است. اولین نفری باشید که نظر می‌دهید!</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: var(--surface-soft); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: bold; font-size: 14px; color: var(--text);"><?php echo e($rev['user_name']); ?></span>
                            <span style="color: #F59E0B; font-weight: bold; font-size: 13px;">★ <?php echo to_persian_num($rev['rating']); ?></span>
                        </div>
                        <p style="font-size: 13px; color: var(--text); line-height: 1.7;"><?php echo e($rev['comment']); ?></p>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 8px; text-align: left;"><?php echo to_persian_num(date('Y/m/d', strtotime($rev['created_at']))); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- New Review Form -->
        <div style="background: var(--surface-soft); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px;">
            <h3 style="font-size: 15px; font-weight: bold; margin-bottom: 16px; color: var(--text);">ثبت نظر و امتیاز جدید</h3>
            
            <form method="POST" action="product?id=<?php echo $product['id']; ?>#reviews-section">
                <?php echo csrf_field(); ?>
                <?php echo honeypot_field(); ?>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>نام و نام خانوادگی *</label>
                        <input type="text" name="user_name" class="form-input" placeholder="نام شما" required>
                    </div>

                    <div class="form-group">
                        <label>امتیاز شما به محصول (از ۱ تا ۵ ستاره) *</label>
                        <select name="rating" class="form-select" required style="color:#F59E0B; font-weight:bold;">
                            <option value="5">★★★★★ (۵ - عالی و فوق‌العاده)</option>
                            <option value="4">★★★★☆ (۴ - خوب)</option>
                            <option value="3">★★★☆☆ (۳ - معمولی)</option>
                            <option value="2">★★☆☆☆ (۲ - ضعیف)</option>
                            <option value="1">★☆☆☆☆ (۱ - بد)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>متن نظر شما *</label>
                    <textarea name="comment" class="form-input" rows="3" placeholder="تجربه خود از خرید و کیفیت این محصول را بنویسید..." required></textarea>
                </div>

                <button type="submit" name="submit_review" value="1" class="action-btn btn-login" style="padding: 10px 24px; font-size: 14px;">
                    ثبت دیدگاه
                </button>
            </form>
        </div>
    </div>

    <!-- Recommended Products -->
    <?php if (!empty($relatedProducts)): ?>
        <section style="margin-bottom: 40px;">
            <div class="section-header">
                <h2 class="section-title">محصولات پیشنهادی</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $rp): ?>
                    <?php $effP = ($rp['discount_price'] > 0) ? $rp['discount_price'] : $rp['price']; ?>
                    <div class="product-card">
                        <a href="product?id=<?php echo $rp['id']; ?>" class="product-card-link">
                            <div class="product-image-wrap">
                                <img src="<?php echo e($rp['image']); ?>" alt="<?php echo e($rp['name']); ?>" loading="lazy">
                            </div>
                            <div class="product-title"><?php echo e($rp['name']); ?></div>
                            <div class="product-price-box">
                                <span class="product-current-price"><?php echo format_price($effP); ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<style>
@media (max-width: 768px) {
    .product-detail-layout {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
        padding: 16px !important;
    }
}
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
