<?php
/**
 * Atlas Shop Database Installer & Seeder (نصب‌کننده خودکار دیتابیس روی هاست)
 */

if (file_exists(__DIR__ . '/data/install.lock')) {
    die('Installation is already complete. Please remove data/install.lock to reinstall.');
}

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';

session_start();

if (!is_admin_logged_in()) {
    die('Unauthorized: Admin login required to run installation.');
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    verify_csrf();
    $db = get_db();
    if (!$db) {
        $message = 'خطا در برقراری ارتباط با دیتابیس. لطفاً مشخصات DB_NAME، DB_USER و DB_PASS را در فایل inc/config.php بررسی نمایید.';
    } else {
        try {
            // Read Schema SQL
            $sqlFile = PATH_ROOT . '/data/schema.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception('فایل data/schema.sql یافت نشد.');
            }

            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                init_sqlite_db($db);
            } else {
                // MySQL / MariaDB on real host
                $sql = file_get_contents($sqlFile);
                $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $db->exec($query);
                    }
                }
                $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
            }

            $now = date('Y-m-d H:i:s');

            // Default Settings
            $defaultSettings = [
                'site_name' => 'اطلس شاپ',
                'site_title' => 'اطلس شاپ | مرجع تخصصی خرید لوازم جانبی موبایل و گجت‌های هوشمند',
                'announcement_bar' => 'ارسال رایگان برای خریدهای بالای ۱,۰۰۰,۰۰۰ تومان | ضمانت ۷ روزه بازگشت کالا',
                'contact_phone' => '۰۲۱-۱۲۳۴۵۶۷۸',
                'contact_mobile' => '۰۹۹۰۱۲۳۴۵۶۷',
                'contact_email' => 'info@atlasshop.ir',
                'contact_address' => 'تهران، خیابان میرداماد، پلاک ۱۲۴ | انبار قم: خیابان صفائیه',
                'about_content' => '<h3>درباره فروشگاه اینترنتی اطلس شاپ</h3><p>فروشگاه اینترنتی «اطلس شاپ» مرجع تخصصی عرضه مستقیم جدیدترین و اورجینال‌ترین لوازم جانبی موبایل، گجت‌های هوشمند، شارژرهای اصلی و پاوربانک با ضمانت ۷ روزه بازگشت کالا و ضمانت اصالت در ایران می‌باشد.</p>',
                'faq_content' => '<h3>پرسش‌های متداول خریداران</h3><h4>۱. روند ثبت سفارش و پرداخت چگونه است؟</h4><p>کافیست محصول مورد نظر خود را به سبد خرید اضافه کنید، سپس با شماره همراه وارد شده و آدرس تحویل را تکمیل نمایید.</p><h4>۲. زمان و نحوه ارسال سفارشات چقدر است؟</h4><p>ارسال پستی پیشتاز ظرف ۲ تا ۴ روز کاری به سراسر کشور تحویل می‌شود. برای استان قم ارسال با پیک شهری سریع نیز فعال می‌باشد.</p>',
                'hero_title_1' => 'شارژ سریع، همیشه همراه شما',
                'hero_subtitle_1' => 'انواع شارژرهای اصلی با ضمانت و بهترین قیمت',
                'zarinpal_active' => '1',
                'zarinpal_sandbox' => '1',
                'zarinpal_merchant_id' => '46018268-3862-11e8-ac86-000c295eb8fc',
                'sms_mock_mode' => '1',
                'sms_provider' => 'mock',
                'sms_api_key' => '',
                'sms_pattern' => ''
            ];

            foreach ($defaultSettings as $k => $v) {
                set_setting($k, $v);
            }

            // Create Default Admin (admin / admin123)
            $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = 'admin'");
            $stmt->execute();
            $adminId = $stmt->fetchColumn();

            if (!$adminId) {
                $stmt = $db->prepare("INSERT INTO admins (username, password_hash, full_name, email, created_at) VALUES ('admin', ?, 'مدیر سیستم', 'admin@atlasshop.ir', ?)");
                $stmt->execute([$adminPass, $now]);
            } else {
                $stmt = $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $stmt->execute([$adminPass, $adminId]);
            }

            // Seed Categories
            $categories = [
                ['name' => 'شارژر و آداپتور', 'slug' => 'chargers', 'icon' => 'zap', 'image' => 'assets/images/cat-chargers.webp'],
                ['name' => 'کابل و تبدیل', 'slug' => 'cables', 'icon' => 'link', 'image' => 'assets/images/cat-cables.webp'],
                ['name' => 'ساعت هوشمند', 'slug' => 'smartwatches', 'icon' => 'watch', 'image' => 'assets/images/cat-smartwatches.webp'],
                ['name' => 'هدفون و هندزفری', 'slug' => 'headphones', 'icon' => 'headphones', 'image' => 'assets/images/cat-headphones.webp'],
                ['name' => 'گجت و ابزار', 'slug' => 'gadgets', 'icon' => 'grid', 'image' => 'assets/images/cat-gadgets.webp'],
                ['name' => 'هولدر و پایه', 'slug' => 'holders', 'icon' => 'smartphone', 'image' => 'assets/images/cat-holders.webp'],
                ['name' => 'پاوربانک', 'slug' => 'powerbanks', 'icon' => 'battery-charging', 'image' => 'assets/images/cat-powerbanks.webp'],
                ['name' => 'قاب و کاور', 'slug' => 'cases', 'icon' => 'shield', 'image' => 'assets/images/cat-cases.webp']
            ];

            $catMap = [];
            foreach ($categories as $idx => $c) {
                $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                $stmt->execute([$c['slug']]);
                $existingCatId = $stmt->fetchColumn();

                if (!$existingCatId) {
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, image, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)");
                    $stmt->execute([$c['name'], $c['slug'], $c['icon'], $c['image'], $idx + 1, $now]);
                    $catMap[$c['slug']] = $db->lastInsertId();
                } else {
                    $stmt = $db->prepare("UPDATE categories SET name = ?, icon = ?, image = ? WHERE id = ?");
                    $stmt->execute([$c['name'], $c['icon'], $c['image'], $existingCatId]);
                    $catMap[$c['slug']] = $existingCatId;
                }
            }

            // Seed Banners
            $banners = [
                ['title' => 'بنر هیرو ۱', 'image' => 'assets/images/hero-1.webp', 'link' => 'shop?category=chargers', 'position' => 'hero', 'order' => 1],
                ['title' => 'بنر هیرو ۲', 'image' => 'assets/images/hero-2.webp', 'link' => 'shop?category=headphones', 'position' => 'hero', 'order' => 2],
                ['title' => 'بنر هیرو ۳', 'image' => 'assets/images/hero-3.webp', 'link' => 'shop?category=smartwatches', 'position' => 'hero', 'order' => 3],
                ['title' => 'تجربه بی‌سیم واقعی', 'image' => 'assets/images/promo-1.webp', 'link' => 'shop?category=headphones', 'position' => 'promo', 'order' => 1],
                ['title' => 'صدایی متفاوت با بهترین برندها', 'image' => 'assets/images/promo-2.webp', 'link' => 'shop?category=headphones', 'position' => 'promo', 'order' => 2]
            ];

            $db->exec("DELETE FROM banners");
            foreach ($banners as $b) {
                $stmt = $db->prepare("INSERT INTO banners (title, image, link, position, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)");
                $stmt->execute([$b['title'], $b['image'], $b['link'], $b['position'], $b['order'], $now]);
            }

            // Seed Sample Products
            $products = [
                [
                    'cat' => 'headphones',
                    'name' => 'ایرپاد پرو ۲ مدل Type-C',
                    'slug' => 'airpods-pro-2-type-c',
                    'brand' => 'Apple',
                    'price' => 7540000,
                    'discount_price' => 6290000,
                    'discount_percent' => 16,
                    'stock' => 25,
                    'rating' => 4.9,
                    'reviews' => 142,
                    'image' => 'uploads/airpods-pro-2.webp',
                    'highlights' => 'میکروفون HD, بلوتوث ۵.۲, باتری ۳۰ ساعت, حذف نویز ANC',
                    'is_featured' => 1,
                    'is_new' => 1,
                    'desc' => 'ایرپاد پرو نسل ۲ با درگاه تایپ سی، حذف نویز فعال پیشرفته (ANC) و کیفیت صدای بی‌نظیر. باتری تا ۶ ساعت پخش مداوم و شارژ کیس تا ۳۰ ساعت.'
                ],
                [
                    'cat' => 'powerbanks',
                    'name' => 'پاوربانک ۲۰۰۰۰ انکر مدل PowerCore',
                    'slug' => 'anker-powerbank-20000',
                    'brand' => 'Anker',
                    'price' => 3450000,
                    'discount_price' => 2950000,
                    'discount_percent' => 14,
                    'stock' => 18,
                    'rating' => 4.8,
                    'reviews' => 98,
                    'image' => 'uploads/anker-powerbank-20000.webp',
                    'highlights' => 'ظرفیت ۲۰۰۰۰mAh, پورت تایپ سی, فست شارژ PD, ایمنی کامل',
                    'is_featured' => 1,
                    'is_new' => 0,
                    'desc' => 'پاوربانک با ظرفیت ۲۰۰۰۰ میلی‌آمپر ساعت با پشتیبانی از فست شارژ PowerIQ و خروجی تایپ سی ۲۰ وات. ایمنی کامل در برابر نوسانات ولتاژ.'
                ],
                [
                    'cat' => 'cases',
                    'name' => 'قاب مگ سیف آیفون ۱۴ پرو',
                    'slug' => 'iphone-14-pro-magsafe-case',
                    'brand' => 'Apple',
                    'price' => 1200000,
                    'discount_price' => 990000,
                    'discount_percent' => 17,
                    'stock' => 40,
                    'rating' => 4.7,
                    'reviews' => 65,
                    'image' => 'uploads/iphone-magsafe-case.webp',
                    'highlights' => 'آهنربای MagSafe, محافظ لنزدار, ژله‌ای شفاف, ضد ضربه',
                    'is_featured' => 1,
                    'is_new' => 0,
                    'desc' => 'کاور ژله‌ای شفاف محافظ لنزدار با آهنربای قوی MagSafe سازگار با تمام شارژرهای بی‌سیم استاندارد.'
                ],
                [
                    'cat' => 'cables',
                    'name' => 'کابل تایپ‌سی به لایتنینگ اصلی ۱ متری',
                    'slug' => 'typec-lightning-cable-1m',
                    'brand' => 'Apple',
                    'price' => 380000,
                    'discount_price' => 290000,
                    'discount_percent' => 23,
                    'stock' => 100,
                    'rating' => 4.6,
                    'reviews' => 210,
                    'image' => 'uploads/typec-lightning-cable.webp',
                    'highlights' => 'روکش بافته شده, فست شارژ ۲۰W PD, طول ۱ متر, مقاوم در برابر خمیدگی',
                    'is_featured' => 1,
                    'is_new' => 0,
                    'desc' => 'کابل اورجینال روکش بافته شده مقاوم در برابر قطعی و تاب خوردگی. پشتیبانی از شارژ سریع ۲۰ وات PD.'
                ],
                [
                    'cat' => 'smartwatches',
                    'name' => 'ساعت هوشمند HK9 Pro',
                    'slug' => 'hk9-pro-smartwatch',
                    'brand' => 'HK',
                    'price' => 4900000,
                    'discount_price' => 4250000,
                    'discount_percent' => 13,
                    'stock' => 12,
                    'rating' => 4.7,
                    'reviews' => 88,
                    'image' => 'uploads/hk9-pro-smartwatch.webp',
                    'highlights' => 'صفحه AMOLED, مکالمه مستقیم, پشتیبانی فارسی, سنسور سلامت',
                    'is_featured' => 1,
                    'is_new' => 1,
                    'desc' => 'ساعت هوشمند با صفحه نمایش AMOLED شفاف، مکالمه مستقیم، پشتیبانی کامل از زبان فارسی و سنسورهای دقیق سلامت.'
                ],
                [
                    'cat' => 'chargers',
                    'name' => 'شارژر دیواری ۶۷ وات شیائومی',
                    'slug' => 'xiaomi-67w-wall-charger',
                    'brand' => 'Xiaomi',
                    'price' => 1550000,
                    'discount_price' => 1290000,
                    'discount_percent' => 16,
                    'stock' => 30,
                    'rating' => 4.8,
                    'reviews' => 175,
                    'image' => 'uploads/charger-67w.webp',
                    'highlights' => 'توان ۶۷ وات سوپرفست, کابل ۶ آمپر, اصلی شیائومی, ایمنی ولتاژ',
                    'is_featured' => 1,
                    'is_new' => 1,
                    'desc' => 'آداپتور شارژ سریع ۶۷ وات اورجینال شیائومی همراه با کابل ۶ آمپری. شارژ صفر تا ۱۰۰ درصد در کمتر از ۴۰ دقیقه.'
                ],
                [
                    'cat' => 'headphones',
                    'name' => 'هدفون بی‌سیم سونی مدل WH-1000XM5',
                    'slug' => 'sony-wh-1000xm5',
                    'brand' => 'SONY',
                    'price' => 15900000,
                    'discount_price' => 13490000,
                    'discount_percent' => 15,
                    'stock' => 8,
                    'rating' => 4.8,
                    'reviews' => 345,
                    'image' => 'uploads/sony-wh1000xm5.webp',
                    'highlights' => 'حذف نویز فعال, بلوتوث ۵.۲, باتری ۳۰ ساعت, درایور ۳۰mm',
                    'is_featured' => 1,
                    'is_new' => 1,
                    'desc' => 'یکی از بهترین هدفون‌های حذف نویز بازار با کیفیت فوق‌العاده و طراحی ارگونومیک. باتری تا ۳۰ ساعت شارژدهی، بلوتوث ۵.۲ و حذف نویز فعال ANC.'
                ]
            ];

            $db->exec("DELETE FROM products");
            foreach ($products as $p) {
                $catId = $catMap[$p['cat']] ?? 1;
                $specs = json_encode([
                    'نوع اتصال' => 'بی‌سیم و با سیم',
                    'نسخه بلوتوث' => '۵.۲',
                    'زمان شارژدهی' => 'تا ۳۰ ساعت',
                    'وزن' => '۲۵۰ گرم'
                ], JSON_UNESCAPED_UNICODE);

                $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, brand, price, discount_price, discount_percent, stock, rating, reviews_count, description, highlights, specifications, is_featured, is_new, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $catId, $p['name'], $p['slug'], $p['brand'], $p['price'], $p['discount_price'], $p['discount_percent'], $p['stock'], $p['rating'], $p['reviews'], $p['desc'], $p['highlights'], $specs, $p['is_featured'], $p['is_new'], $p['image'], $now
                ]);
            }

            // Seed Coupons
            $db->exec("DELETE FROM coupons");
            $db->prepare("INSERT INTO coupons (code, type, value, min_order_amount, max_discount, usage_limit, times_used, is_active, created_at) VALUES ('ATLAS10', 'percent', 10, 500000, 1000000, 500, 12, 1, ?)")->execute([$now]);
            $db->prepare("INSERT INTO coupons (code, type, value, min_order_amount, max_discount, usage_limit, times_used, is_active, created_at) VALUES ('SUMMER20', 'fixed', 500000, 2000000, 500000, 200, 45, 1, ?)")->execute([$now]);

            // Seed 4 SEO Blog Posts (including local Qom SEO article)
            $db->exec("DELETE FROM posts");
            
            $post1 = [
                'title' => 'راهنمای جامع خرید پاوربانک و شارژر همراه در سال ۱۴۰۳',
                'slug' => 'powerbank-buying-guide-1403',
                'excerpt' => 'بررسی تخصصی نکات حیاتی قبل از خرید پاوربانک شامل ظرفیت واقعی (mAh)، پروتکل‌های شارژ سریع PD و معرفی برترین برندهای بازار.',
                'body' => '<h3>نکات کلیدی در انتخاب بهترین پاوربانک</h3><p>پاوربانک‌ها به یکی از ضروری‌ترین گجت‌های روزمره تبدیل شده‌اند. هنگام خرید باید به ظرفیت اسمی و واقعی (mAh)، توان خروجی (وات)، فناوری‌های فست شارژ نظیر Power Delivery و Quick Charge دقت کنید.</p><h4>۱. ظرفیت واقعی در برابر ظرفیت اسمی</h4><p>همواره به یاد داشته باشید که بازدهی باتری‌های پاوربانک حدود ۷۰ الی ۸۰ درصد است. برای مثال یک پاوربانک ۱۰,۰۰۰ میلی‌آمپر ساعتی، حدود ۷,۵۰۰ میلی‌آمپر شارژ واقعی ارائه می‌دهد.</p>',
                'image' => 'assets/images/post-powerbank.webp',
                'seo_title' => 'راهنمای خرید بهترین پاوربانک ۱۴۰۳ - اطلس شاپ',
                'meta' => 'راهنمای جامع خرید انواع پاوربانک انکر، شیائومی و باسئوس همراه با بررسی ظرفیت واقعی و فست شارژ PD.'
            ];

            $post2 = [
                'title' => '۱۰ نکته حیاتی که قبل از خرید هدفون و هندزفری بلوتوثی باید بدانید',
                'slug' => 'headphone-buying-guide-tips',
                'excerpt' => 'بررسی کامل فناوری حذف نویز فعال (ANC)، نسخه بلوتوث ۵.۳، کدک‌های صوتی باکیفیت و ارگونومی قبل از خرید هدفون.',
                'body' => '<h3>تکنولوژی حذف نویز فعال (ANC) چگونه کار می‌کند؟</h3><p>سیستم ANC با استفاده از میکروفون‌های داخلی و بیرونی، امواج صوتی محیط را دریافت کرده و با ایجاد موج صوتی معکوس، صدای مزاحم اطراف را خنثی می‌کند.</p><h4>۱. نسخه بلوتوث و پایداری اتصال</h4><p>هدفون‌های دارای بلوتوث ۵.۲ و ۵.۳ مصرف انرژی بسیار کمتری دارند و تاخیر صدا (Latency) را در بازی‌ها و ویدیوها به حداقل می‌رسانند.</p>',
                'image' => 'assets/images/post-headphones.webp',
                'seo_title' => '۱۰ نکته مهم قبل از خرید هندزفری بلوتوثی - اطلس شاپ',
                'meta' => 'راهنمای انتخاب و خرید هندزفری بی‌سیم سونی، ایرپاد اپل و بوز با قابلیت حذف نویز ANC.'
            ];

            $post3 = [
                'title' => 'بررسی و مقایسه قابلیت‌های جذاب ساعت‌های هوشمند جدید',
                'slug' => 'smartwatch-features-comparison',
                'excerpt' => 'مقایسه جامع امکانات ساعت‌های هوشمند شامل نمایشگر AMOLED، قابلیت مکالمه مستقیم، پشتیبانی فارسی و سنسورهای سلامت.',
                'body' => '<h3>ویژگی‌های ضروری یک ساعت هوشمند حرفه‌ای</h3><p>ساعت‌های هوشمند امروزی فراتر از یک نمایشگر زمان، دستیار سلامتی و تناسب اندام همراه شما هستند.</p><h4>۱. نمایشگر AMOLED با کیفیت بالا</h4><p>نمایشگرهای AMOLED زاویه دید عالی و روشنایی فوق‌العاده‌ای در زیر نور مستقیم خورشید ارائه می‌دهند.</p>',
                'image' => 'assets/images/post-smartwatch.webp',
                'seo_title' => 'راهنمای انتخاب و خرید ساعت هوشمند - اطلس شاپ',
                'meta' => 'مقایسه قابلیت‌های جدیدترین ساعت‌های هوشمند HK، سامسونگ و اپل واچ همراه با سنسورهای پایش سلامت.'
            ];

            $post4 = [
                'title' => 'مرجع تخصصی خرید لوازم جانبی موبایل و گجت در شهر قم (ارسال سریع با پیک)',
                'slug' => 'mobile-accessories-qom-buying-guide',
                'excerpt' => 'خرید آنلاین و سریع لوازم جانبی اصلی اپل، شیائومی و انکر در استان قم با تحویل اکسپرس پیک موتوری زیر ۲ ساعت.',
                'body' => '<h3>خرید آنلاین و سریع لوازم جانبی اصلی در استان قم</h3><p>امروزه دسترسی به <b>لوازم جانبی اورجینال موبایل</b> با ضمانت تعویض و ارسال سریع از دغدغه‌های اصلی خریداران در شهر قم است. فروشگاه اطلس شاپ با داشتن انبار مرکزی در قم، امکان ارسال فوری با پیک موتوری زیر ۲ ساعت را برای تمام همشهریان گرامی فراهم آورده است.</p><h4>مزایای خرید از اطلس شاپ در قم:</h4><ul><li>ارسال با پیک موتوری شهری در کمتر از ۲ ساعت</li><li>تضمین ۱۰۰٪ اصالت کالاهای اپل، انکر و شیائومی</li><li>امکان پرداخت آنلاین امن یا دریافت حضوری</li></ul><h4>دسته‌بندی‌های پرطرفدار در قم:</h4><p>برای مشاهده و خرید کالاها می‌توانید از لینک‌های زیر استفاده کنید:</p><p>• <a href="shop?category=chargers"><b>خرید انواع شارژر و آداپتور اصلی ۶۷ وات و ۲۰ وات</b></a><br>• <a href="shop?category=powerbanks"><b>خرید پاوربانک ۲۰۰۰۰ فست شارژ انکر و باسئوس</b></a><br>• <a href="categories"><b>مشاهده تمام دسته‌بندی‌های محصولات اطلس شاپ</b></a></p>',
                'image' => 'assets/images/post-qom-store.webp',
                'seo_title' => 'خرید لوازم جانبی موبایل در قم - اطلس شاپ',
                'meta' => 'خرید آنلاین انواع کابل، شارژر، پاوربانک و هندزفری در استان قم با ارسال سریع پیک موتوری زیر ۲ ساعت.'
            ];

            $allPosts = [$post1, $post2, $post3, $post4];
            foreach ($allPosts as $p) {
                $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, body, image, seo_title, meta_description, author, status, views, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'تیم تحریریه اطلس شاپ', 'published', 210, ?)");
                $stmt->execute([$p['title'], $p['slug'], $p['excerpt'], $p['body'], $p['image'], $p['seo_title'], $p['meta'], $now]);
            }

            $success = true;
            $message = 'پایگاه داده اطلس شاپ با موفقیت ایجاد و داده‌های اولیه ثبت گردیدند!';
            file_put_contents(__DIR__ . '/data/install.lock', 'locked');

        } catch (Exception $e) {
            $message = 'خطا هنگام ایجاد جداول دیتابیس: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب دیتابیس اطلس شاپ روی هاست</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #F7F9FC;
            color: #17233D;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .install-box {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #E3EAF4;
            padding: 40px;
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .logo { font-size: 28px; font-weight: bold; color: #2F67E8; margin-bottom: 16px; }
        .btn {
            background: #2F67E8;
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn:hover { background: #1E4FBD; }
        .alert { padding: 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; text-align: right; }
        .alert-success { background: #EBF9F1; color: #2D9A5B; border: 1px solid #B8E8D0; }
        .alert-danger { background: #FDE8E8; color: #D7505D; border: 1px solid #F8B8B8; }
        .db-info {
            background: #F1F5F9;
            padding: 14px;
            border-radius: 10px;
            text-align: right;
            margin-bottom: 24px;
            font-size: 13px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="install-box">
        <div class="logo">AtlasShop</div>
        <h2>نصب و راه اندازی دیتابیس اطلس شاپ روی هاست</h2>

        <div class="db-info">
            <strong>تنظیمات اتصال فعلی (از فایل inc/config.php):</strong><br>
            • سرور: <code><?php echo DB_HOST; ?></code><br>
            • نام دیتابیس: <code><?php echo DB_NAME; ?></code><br>
            • نام کاربری: <code><?php echo DB_USER; ?></code>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo e($message); ?>
            </div>
            <?php if ($success): ?>
                <div class="db-info">
                    <strong>اطلاعات ورود به پنل مدیریت اختصاصی:</strong><br>
                    آدرس پنل: <a href="panel-adminweb/login.php">panel-adminweb/login.php</a><br>
                    نام کاربری: <code>admin</code><br>
                    رمز عبور: <code>admin123</code>
                </div>
                <br>
                <a href="index.php" class="btn">مشاهده فروشگاه</a>
                <a href="panel-adminweb/index.php" class="btn" style="background:#17233D; margin-right:8px;">ورود به پنل مدیریت</a>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn">نصب و ساخت جداول دیتابیس</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
