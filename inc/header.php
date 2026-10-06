<?php
/**
 * Main Front-end Header Component
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$db = get_db();
$announcement = get_setting('announcement_bar', 'ارسال رایگان برای خریدهای بالای ۱,۰۰۰,۰۰۰ تومان | ضمانت ۷ روزه بازگشت کالا');
$siteName = get_setting('site_name', 'اطلس شاپ');
$siteLogo = get_setting('site_logo', 'assets/images/logo.svg');

// Fetch Categories for Header Nav & Mega Menu
$navCategories = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT id, name, slug, icon, image FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
        $navCategories = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Calculate Cart Item Count
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

$isLoggedIn = is_customer_logged_in();
$customer = current_customer();
$activePage = $activePage ?? '';
$isDarkTheme = $isDarkTheme ?? false;

// Profile/Orders link for logged in user vs login link for guest
$userDestinationUrl = $isLoggedIn ? SITE_URL . '/cart#previous-orders' : SITE_URL . '/login';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo SITE_URL; ?>/<?php echo e($siteLogo); ?>" type="image/svg+xml">
    <?php echo render_seo_tags($seoParams ?? []); ?>
    <?php if (($activePage ?? '') === 'home' && function_exists('seo_organization_schema')) {
        seo_organization_schema();
        seo_website_schema();
    } ?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css?v=<?php echo ASSETS_VER; ?>">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>/<?php echo $css; ?>?v=<?php echo ASSETS_VER; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        window.SITE_URL = "<?php echo SITE_URL; ?>";
    </script>
</head>
<body class="<?php echo $isDarkTheme ? 'dark-theme' : ''; ?>">

    <!-- Top Announcement Bar -->
    <?php if (!empty($announcement)): ?>
        <div class="announcement-bar">
            <?php echo e($announcement); ?>
        </div>
    <?php endif; ?>

    <!-- Main Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-top">
                <!-- Brand Logo (Links to root /) -->
                <a href="<?php echo SITE_URL; ?>/" class="brand-logo">
                    <img src="<?php echo SITE_URL; ?>/<?php echo e($siteLogo); ?>" alt="<?php echo e($siteName); ?>">
                </a>

                <!-- Desktop Inline Search Box -->
                <div class="header-search desktop-search-box">
                    <input type="text" id="header-search-input" placeholder="جستجو در محصولات، برندها و..." autocomplete="off">
                    <span class="search-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <div id="search-results-dropdown" class="search-results-dropdown"></div>
                </div>

                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Mobile Search Trigger Icon -->
                    <button type="button" id="mobile-search-toggle-btn" class="action-btn cart-trigger mobile-search-icon-btn" title="جستجو">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Desktop Login/Profile Button (Text + Icon on Desktop) -->
                    <a href="<?php echo $userDestinationUrl; ?>" class="action-btn btn-login desktop-user-btn">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="login-btn-text">
                            <?php echo $isLoggedIn ? e($customer['full_name'] ?: $customer['mobile']) : 'ورود / ثبت‌نام'; ?>
                        </span>
                    </a>

                    <!-- Mobile User Icon Only (Icon only on Mobile) -->
                    <a href="<?php echo $userDestinationUrl; ?>" class="action-btn cart-trigger mobile-user-icon-btn" title="<?php echo $isLoggedIn ? 'سفارش‌های قبلی شما' : 'ورود / ثبت‌نام'; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>

                    <!-- Cart Trigger -->
                    <button type="button" class="action-btn cart-trigger cart-trigger-btn" title="سبد خرید">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="cart-badge cart-badge-count"><?php echo to_persian_num($cartCount); ?></span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar Panel -->
            <div id="mobile-search-panel" class="mobile-search-panel">
                <div style="position: relative; display: flex; align-items: center; gap: 8px; width: 100%;">
                    <input type="text" id="mobile-search-input" placeholder="جستجو در محصولات، برندها و..." autocomplete="off">
                    <button type="button" id="close-mobile-search-btn" style="background: none; border: none; font-size: 22px; color: var(--muted); cursor: pointer; padding: 0 4px;">&times;</button>
                </div>
                <div id="mobile-search-results-dropdown" class="search-results-dropdown" style="top: 100%; position: absolute; right: 0; left: 0;"></div>
            </div>

            <!-- Navigation Bar -->
            <nav class="header-nav">
                <div class="nav-item">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>دسته‌بندی محصولات</span>
                    
                    <!-- Mega Menu -->
                    <div class="mega-menu">
                        <?php foreach ($navCategories as $cat): ?>
                            <div>
                                <a href="<?php echo SITE_URL; ?>/shop?category=<?php echo e($cat['slug']); ?>" class="mega-cat-title">
                                    <?php echo e($cat['name']); ?>
                                </a>
                                <ul class="mega-sub-list">
                                    <li><a href="<?php echo SITE_URL; ?>/shop?category=<?php echo e($cat['slug']); ?>">مشاهده همه کالاها</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/shop?category=<?php echo e($cat['slug']); ?>&brand=Apple">محصولات اپل</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/shop?category=<?php echo e($cat['slug']); ?>&brand=Anker">محصولات انکر</a></li>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="<?php echo SITE_URL; ?>/" class="nav-item <?php echo $activePage === 'home' ? 'active' : ''; ?>">خانه</a>
                <a href="<?php echo SITE_URL; ?>/shop" class="nav-item <?php echo $activePage === 'shop' ? 'active' : ''; ?>">فروشگاه</a>
                <a href="<?php echo SITE_URL; ?>/blog" class="nav-item <?php echo $activePage === 'blog' ? 'active' : ''; ?>">وبلاگ و اخبار</a>
                <a href="<?php echo SITE_URL; ?>/about" class="nav-item <?php echo $activePage === 'about' ? 'active' : ''; ?>">درباره ما</a>
                <a href="<?php echo SITE_URL; ?>/contact" class="nav-item <?php echo $activePage === 'contact' ? 'active' : ''; ?>">تماس با ما</a>
            </nav>
        </div>
    </header>

    <!-- Slide-out Cart Drawer -->
    <div id="cart-drawer-overlay" class="cart-drawer-overlay"></div>
    <div id="cart-drawer" class="cart-drawer">
        <div class="cart-drawer-header">
            <h3>سبد خرید شما</h3>
            <button type="button" id="close-drawer-btn" class="close-drawer-btn">&times;</button>
        </div>
        <div id="cart-drawer-body-items" class="cart-drawer-body">
            <!-- Items dynamically loaded via AJAX JS -->
        </div>
        <div class="cart-drawer-footer">
            <div class="drawer-summary-row">
                <span>مبلغ قابل پرداخت:</span>
                <span id="cart-drawer-subtotal" style="color:var(--primary);">۰ تومان</span>
            </div>
            <div class="drawer-actions">
                <a href="<?php echo SITE_URL; ?>/checkout" class="btn-checkout-drawer">تکمیل خرید</a>
                <a href="<?php echo SITE_URL; ?>/cart" class="btn-view-cart-drawer">مشاهده سبد</a>
            </div>
        </div>
    </div>
