# راهنمای جامع اطلس شاپ (Atlas Shop)

پروژه **اطلس شاپ** یک سیستم فروشگاهی آنلاین پیشرفته، امن و بدون فریم‌ورک بر پایه **PHP خام (Native PHP)** و دیتابیس **MySQL / MariaDB** می‌باشد.

---

## 🔒 استانداردهای امنیتی پیاده‌سازی شده

1. **آدرس پنل مدیریت اختصاصی و مخفی**: آدرس ورود مدیریت از مسیر عمومی `/admin` به مسیر اختصاصی **`/panel-adminweb/`** تغییر داده شده است.
2. **محافظت در برابر Brute Force (حملات دیکشنری)**: قفل هوشمند ۵ بار تلاش ناموفق در لاگین مدیریت و محدودیت ۶۰ ثانیه‌ای ارسال کد OTP.
3. **امنیت کوکی و کوئری‌ها**:
   - استفاده ۱۰۰٪ از **PDO Prepared Statements** جهت مسدودسازی کامل آسیب‌پذیری SQL Injection.
   - خنثی‌سازی کامل ورودی‌ها با `htmlspecialchars` جهت مقابله با کدهای مخرب XSS.
   - تنظیمات کوکی امن `HttpOnly`, `SameSite=Lax` و `Strict Mode` جهت جلوگیری از Session Hijacking.
   - توکن‌های تصادفی **CSRF** و تله‌های مخفی **Honeypot**.
   - هدرهای امنیتی HTTP شامل `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`.

---

## 🧩 ماژول‌های اختصاصی اضافه شده

1. **ماژول ورود پیامکی OTP (`panel-adminweb/customer-login-settings.php`)**: پشتیبانی از سامانه پیامک کاوه‌نگار، فراز اس‌ام‌اس (IPPanel)، ملی پیامک و حالت آزمایشی Mock.
2. **ماژول اختصاصی اتصال به ترب (`panel-adminweb/torob-settings.php` & `api/torob.php`)**: تولید فید زنده محصولات طبق استانداردهای رسمی ربات ترب همراه با کلید توکن امنیتی.
3. **ماژول مرکز فروشندگان دیجی‌کالا (`panel-adminweb/digikala-settings.php` & `api/digikala.php`)**: صادرکننده زنده قیمت‌ها به ریال و کد SKU ویژه دیجی‌کالا.
4. **مدیریت روش‌های ارسال و پیک (`panel-adminweb/shipping-methods.php`)**: تعیین هزینه ارسال، سقف ارسال رایگان و تفکیک استان‌های مجاز (مانند فعال‌سازی پیک شهری فقط برای استان قم).

---

## 🚀 مراحل نصب روی هاست واقعی (cPanel / DirectAdmin)

1. فایل زیپ `atlas-shop.zip` را در مسیر `public_html` آپلود کرده و **Extract** کنید.
2. یک دیتابیس جدید در cPanel بسازید.
3. مشخصات اتصال دیتابیس را در فایل `inc/config.php` قرار دهید:
   ```php
   define('DB_NAME', 'atlashop_db');
   define('DB_USER', 'atlashop_admin');
   define('DB_PASS', 'Mohammadi@2007samimi');
   ```
4. آدرس زیر را در مرورگر باز کنید و دکمه **«نصب و ساخت جداول دیتابیس»** را بزنید:
   ```text
   https://yourdomain.com/install.php
   ```

---

## 🔑 اطلاعات ورود به پنل مدیریت اختصاصی

- **آدرس پنل مدیریت**: `https://yourdomain.com/panel-adminweb/login.php`
- **نام کاربری**: `admin`
- **رمز عبور اولیه**: `admin123`
