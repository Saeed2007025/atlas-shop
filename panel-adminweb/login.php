<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $res = admin_login($username, $password);
    if ($res['success']) {
        redirect('index.php');
    } else {
        $error = $res['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت اطلس شاپ</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=<?php echo ASSETS_VER; ?>">
</head>
<body style="align-items: center; justify-content: center; background: var(--admin-bg); color: var(--admin-text); font-family: 'Vazirmatn', sans-serif;">

    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 16px; padding: 40px; max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo-dark.svg" style="height: 42px; margin-bottom: 12px;">
            <h2>ورود مدیر سیستم</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.2); color: var(--admin-red); padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; text-align: center;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>نام کاربری</label>
                <input type="text" name="username" class="form-control" required style="direction: ltr;">
            </div>

            <div class="form-group">
                <label>رمز عبور</label>
                <input type="password" name="password" class="form-control" required style="direction: ltr;">
            </div>

            <button type="submit" class="btn-green-add" style="width: 100%; justify-content: center; padding: 12px; margin-top: 12px;">
                ورود به پنل
            </button>
        </form>
    </div>

</body>
</html>
