<?php
/**
 * Main Front-end Footer Component
 */

$siteName = get_setting('site_name', 'اطلس شاپ');
$phone = get_setting('contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸');
$mobile = get_setting('contact_mobile', '۰۹۹۰۱۲۳۴۵۶۷');
$email = get_setting('contact_email', 'info@atlasshop.ir');
$address = get_setting('contact_address', 'تهران، خیابان میرداماد، پلاک ۱۲۴');
?>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: About -->
                <div class="footer-col">
                    <h4>درباره <?php echo e($siteName); ?></h4>
                    <p style="font-size: 13px; line-height: 1.8; margin-bottom: 16px;">
                        اطلس شاپ، مرجع تخصصی فروش انواع گجت‌های دیجیتال، شارژرهای اورجینال، هندزفری‌های بلوتوثی و پاوربانک با ضمانت ۷ روزه بازگشت کالا و ضمانت اصالت در ایران.
                    </p>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div style="background: #1F2937; padding: 10px; border-radius: 8px; font-size: 11px; text-align: center;">
                            <div style="font-weight: bold; color: #10B981;">نماد اعتماد الکترونیکی</div>
                            <div style="color: #F59E0B; margin-top: 4px;">★ ★ ★ ★ ★</div>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="footer-col">
                    <h4>دسترسی سریع</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/about">درباره ما</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact">تماس با ما</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/blog">وبلاگ و مقالات</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns">شرایط و قوانین</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq">حریم خصوصی</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Service -->
                <div class="footer-col">
                    <h4>خدمات مشتریان</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/faq">پرسش‌های متداول</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/cart">ثبت سفارش</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns">رویه ارسال سفارش</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns">ضمانت و بازگشت کالا</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/checkout">پیگیری سفارش</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact -->
                <div class="footer-col">
                    <h4>راه‌های ارتباطی</h4>
                    <p style="font-size: 13px; margin-bottom: 8px;">تلفن پشتیبانی: <?php echo e($phone); ?></p>
                    <p style="font-size: 13px; margin-bottom: 8px;">شماره همراه: <?php echo e($mobile); ?></p>
                    <p style="font-size: 13px; margin-bottom: 8px;">ایمیل: <?php echo e($email); ?></p>
                    <p style="font-size: 13px; margin-bottom: 8px;">آدرس: <?php echo e($address); ?></p>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                تمامی حقوق این سایت محفوظ و متعلق به <?php echo e($siteName); ?> می‌باشد.
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav">
        <div class="bottom-nav-items">
            <!-- 1. خانه (Rightmost) -->
            <a href="<?php echo SITE_URL; ?>/" class="bottom-nav-item <?php echo $activePage === 'home' ? 'active' : ''; ?>">
                <div class="nav-icon-wrap">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span>خانه</span>
            </a>

            <!-- 2. دسته‌بندی (Second from Right) -->
            <a href="<?php echo SITE_URL; ?>/categories" class="bottom-nav-item <?php echo $activePage === 'categories' ? 'active' : ''; ?>">
                <div class="nav-icon-wrap">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <span>دسته‌بندی</span>
            </a>

            <!-- 3. فروشگاه (Third from Right) -->
            <a href="<?php echo SITE_URL; ?>/shop" class="bottom-nav-item <?php echo $activePage === 'shop' ? 'active' : ''; ?>">
                <div class="nav-icon-wrap">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span>فروشگاه</span>
            </a>

            <!-- 4. سبد خرید (Far Left / Fourth / Last) -->
            <a href="<?php echo SITE_URL; ?>/cart" class="bottom-nav-item <?php echo $activePage === 'cart' ? 'active' : ''; ?>">
                <div class="nav-icon-wrap">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span>سبد خرید</span>
            </a>
        </div>
    </nav>

    <!-- JS Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=<?php echo ASSETS_VER; ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?php echo SITE_URL; ?>/<?php echo $js; ?>?v=<?php echo ASSETS_VER; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
