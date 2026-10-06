<?php
/**
 * Atlas Shop Home Page
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'home';
$seoParams = [
    'title' => 'صفحه اصلی',
    'description' => 'فروشگاه اینترنتی اطلس شاپ - مرجع خرید انواع شارژر، کابل، هندزفری، پاوربانک و گجت‌های هوشمند اصلی با بهترین قیمت و ضمانت اصالت.'
];

$db = get_db();

// Hero Banners
$heroBanners = [];
// Promo Banners
$promoBanners = [];
// Categories
$categories = [];
// Newest Products
$newestProducts = [];
// Best Sellers
$bestSellers = [];
// Blog Posts
$posts = [];

if ($db) {
    try {
        // Fetch Hero Banners
        $stmt = $db->query("SELECT * FROM banners WHERE position = 'hero' AND is_active = 1 ORDER BY display_order ASC LIMIT 3");
        $heroBanners = $stmt->fetchAll();

        // Fetch Promo Banners
        $stmt = $db->query("SELECT * FROM banners WHERE position = 'promo' AND is_active = 1 ORDER BY display_order ASC LIMIT 2");
        $promoBanners = $stmt->fetchAll();

        // Fetch Categories
        $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
        $categories = $stmt->fetchAll();

        // Fetch Newest Products
        $stmt = $db->query("SELECT * FROM products ORDER BY id DESC LIMIT 10");
        $newestProducts = $stmt->fetchAll();

        // Fetch Best Sellers
        $stmt = $db->query("SELECT * FROM products ORDER BY rating DESC, reviews_count DESC LIMIT 10");
        $bestSellers = $stmt->fetchAll();

        // Fetch Posts
        $stmt = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY id DESC LIMIT 3");
        $posts = $stmt->fetchAll();
    } catch (Exception $e) {}
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container">

    <!-- Hero Banner Slider -->
    <?php if (!empty($heroBanners)): ?>
        <section class="hero-slider-section">
            <div class="hero-slider-container">
                <?php foreach ($heroBanners as $idx => $banner): ?>
                    <a href="<?php echo e($banner['link'] ?? 'shop'); ?>" class="hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo e($banner['image']); ?>" alt="<?php echo e($banner['title']); ?>" loading="<?php echo $idx === 0 ? 'eager' : 'lazy'; ?>">
                    </a>
                <?php endforeach; ?>

                <div class="hero-dots">
                    <?php foreach ($heroBanners as $idx => $banner): ?>
                        <span class="hero-dot <?php echo $idx === 0 ? 'active' : ''; ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Trust Badges Row -->
    <section class="trust-badges-bar">
        <div class="trust-badge-card">
            <div class="trust-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="trust-title">ارسال سریع</div>
                <div class="trust-desc">ارسال به سراسر کشور</div>
            </div>
        </div>

        <div class="trust-badge-card">
            <div class="trust-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="trust-title">ضمانت اصالت کالا</div>
                <div class="trust-desc">تضمین ۱۰۰٪ اورجینال</div>
            </div>
        </div>

        <div class="trust-badge-card">
            <div class="trust-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <div class="trust-title">۷ روز ضمانت بازگشت</div>
                <div class="trust-desc">بدون قید و شرط</div>
            </div>
        </div>

        <div class="trust-badge-card">
            <div class="trust-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="trust-title">پشتیبانی ۲۴/۷</div>
                <div class="trust-desc">پاسخگوی شما هستیم</div>
            </div>
        </div>
    </section>

    <!-- Categories Cards Grid -->
    <?php if (!empty($categories)): ?>
        <section class="categories-section">
            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="shop?category=<?php echo e($cat['slug']); ?>" class="category-card">
                        <img src="<?php echo e($cat['image'] ?? 'assets/images/cat-gadgets.webp'); ?>" alt="<?php echo e($cat['name']); ?>" loading="lazy">
                        <div class="category-card-title"><?php echo e($cat['name']); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Slider 1: Newest Products (جدیدترین محصولات) -->
    <section style="margin: 32px 0;">
        <div class="section-header">
            <h2 class="section-title">جدیدترین محصولات</h2>
            <a href="shop?sort=newest" class="btn-view-all">
                <span>مشاهده همه</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        </div>

        <div class="product-slider">
            <?php foreach ($newestProducts as $p): ?>
                <?php $effectivePrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price']; ?>
                <div class="product-card">
                    <?php if ($p['discount_percent'] > 0): ?>
                        <span class="product-badge"><?php echo to_persian_num($p['discount_percent']); ?>٪ تخفیف</span>
                    <?php elseif ($p['is_new']): ?>
                        <span class="product-badge" style="background:#2D9A5B;">جدید</span>
                    <?php endif; ?>

                    <a href="product?id=<?php echo $p['id']; ?>" class="product-card-link">
                        <div class="product-image-wrap">
                            <img src="<?php echo e($p['image']); ?>" alt="<?php echo e($p['name']); ?>" loading="lazy">
                        </div>
                        <div class="product-brand"><?php echo e($p['brand']); ?></div>
                        <div class="product-title"><?php echo e($p['name']); ?></div>
                        <div class="product-rating">
                            ★ <span><?php echo to_persian_num($p['rating']); ?></span>
                        </div>
                        <div class="product-price-box">
                            <?php if ($p['discount_price'] > 0): ?>
                                <span class="product-old-price"><?php echo format_price($p['price'], ''); ?></span>
                            <?php endif; ?>
                            <span class="product-current-price"><?php echo format_price($effectivePrice); ?></span>
                        </div>
                    </a>

                    <div class="product-card-footer">
                        <span style="font-size:12px; color:var(--muted);">موجود در انبار</span>
                        <button type="button" onclick="addToCart(<?php echo $p['id']; ?>)" class="btn-add-cart-icon" title="افزودن به سبد خرید">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Promotional Side-by-Side Banners -->
    <?php if (count($promoBanners) >= 2): ?>
        <section class="promo-banners-section">
            <a href="<?php echo e($promoBanners[0]['link'] ?? 'shop'); ?>" class="promo-banner-card">
                <img src="<?php echo e($promoBanners[0]['image']); ?>" alt="<?php echo e($promoBanners[0]['title']); ?>" loading="lazy">
            </a>
            <a href="<?php echo e($promoBanners[1]['link'] ?? 'shop'); ?>" class="promo-banner-card">
                <img src="<?php echo e($promoBanners[1]['image']); ?>" alt="<?php echo e($promoBanners[1]['title']); ?>" loading="lazy">
            </a>
        </section>
    <?php endif; ?>

    <!-- Slider 2: Best Sellers (پرفروش‌ترین محصولات - اسلایدری افقی یکسان) -->
    <section class="best-sellers-section" style="margin: 32px 0;">
        <div class="section-header">
            <h2 class="section-title">پرفروش‌ترین محصولات</h2>
            <a href="shop?sort=best_selling" class="btn-view-all">
                <span>مشاهده همه</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        </div>

        <div class="product-slider">
            <?php foreach ($bestSellers as $p): ?>
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
                        <span style="font-size:12px; color:var(--muted);">موجود در انبار</span>
                        <button type="button" onclick="addToCart(<?php echo $p['id']; ?>)" class="btn-add-cart-icon" title="افزودن به سبد خرید">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Blog & Articles Preview -->
    <?php if (!empty($posts)): ?>
        <section style="margin: 40px 0;">
            <div class="section-header">
                <h2 class="section-title">راهنمای خرید و اخبار فناوری</h2>
                <a href="blog" class="btn-view-all">
                    <span>مشاهده همه مقالات</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <?php foreach ($posts as $post): ?>
                    <a href="post?slug=<?php echo e($post['slug']); ?>" style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow-sm);">
                        <img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" style="width:100%; aspect-ratio:16/9; object-fit:cover;" loading="lazy">
                        <div style="padding: 16px;">
                            <h3 style="font-size:15px; font-weight:bold; margin-bottom:8px; color:var(--text);"><?php echo e($post['title']); ?></h3>
                            <p style="font-size:13px; color:var(--muted); line-height:1.5; height:38px; overflow:hidden;"><?php echo e($post['excerpt']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Newsletter Card (Responsive Clean Card) -->
    <section class="newsletter-section">
        <div class="newsletter-content">
            <h3>از جدیدترین تخفیف‌ها و محصولات باخبر شوید</h3>
            <p>شماره موبایل یا ایمیل خود را وارد کنید و عضو خبرنامه ما شوید.</p>
        </div>
        <form id="newsletter-form" class="newsletter-form">
            <input type="text" name="contact_input" placeholder="شماره موبایل یا ایمیل خود را وارد کنید..." required autocomplete="off">
            <button type="submit">ثبت اشتراک</button>
        </form>
    </section>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
