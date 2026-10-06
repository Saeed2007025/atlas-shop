<?php
/**
 * Concealed Path Defense
 * Returns fake 404 Not Found if anyone attempts to access /admin
 */
header("HTTP/1.1 404 Not Found");
header("Status: 404 Not Found");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>۴۰۴ - صفحه مورد نظر یافت نشد</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #F7F9FC; color: #17233D; text-align: center; padding: 100px 20px; direction: rtl; }
        h1 { font-size: 48px; color: #2F67E8; margin-bottom: 10px; }
        p { font-size: 16px; color: #7D8CA1; }
        a { color: #2F67E8; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <h1>۴۰۴</h1>
    <h2>صفحه مورد نظر یافت نشد!</h2>
    <p>آدرسی که وارد کرده‌اید وجود ندارد یا منتقل شده است.</p>
    <br>
    <a href="../">بازگشت به صفحه اصلی</a>
</body>
</html>
