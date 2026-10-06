<?php
$adminPage = $adminPage ?? 'dashboard';
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="<?php echo SITE_URL; ?>/admin/index.php">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo-dark.svg" alt="AtlasShop Admin" style="height: 38px;">
        </a>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo $adminPage === 'dashboard' ? 'active' : ''; ?>">
            <a href="index.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>داشبورد اصلی</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'orders' ? 'active' : ''; ?>">
            <a href="orders.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>سفارش‌ها</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'shipping' ? 'active' : ''; ?>">
            <a href="shipping-methods.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>روش‌های ارسال و پیک</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'products' ? 'active' : ''; ?>">
            <a href="products.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>محصولات</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'categories' ? 'active' : ''; ?>">
            <a href="categories.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>دسته‌بندی‌ها</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'customers' ? 'active' : ''; ?>">
            <a href="customers.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 43a8 8 0 100-16 8 8 0 000 16zM21 41a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1h2a1 1 0 001-1v-2z"/></svg>
                <span>مشتریان و خبرنامه</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'sms' ? 'active' : ''; ?>">
            <a href="customer-login-settings.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>ماژول ورود پیامکی (SMS)</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'torob' ? 'active' : ''; ?>">
            <a href="torob-settings.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>ماژول اتصال به ترب (Torob)</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'digikala' ? 'active' : ''; ?>">
            <a href="digikala-settings.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>ماژول دیجی‌کالا (Digikala)</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'coupons' ? 'active' : ''; ?>">
            <a href="coupons.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>کدهای تخفیف</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'reports' ? 'active' : ''; ?>">
            <a href="reports.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>گزارش‌ها</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'content' ? 'active' : ''; ?>">
            <a href="content.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span>مدیریت محتوا (وبلاگ)</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'banners' ? 'active' : ''; ?>">
            <a href="banners.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>بنرهای تبلیغاتی</span>
            </a>
        </li>
        <li class="<?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
            <a href="settings.php">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                <span>تنظیمات عمومی</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-profile">
        <div style="flex:1;">
            <div style="font-weight:bold; font-size:13px; color:#FFF;">مدیر سیستم</div>
            <a href="logout.php" style="font-size:12px; color:var(--admin-red);">خروج از حساب</a>
        </div>
    </div>
</aside>
