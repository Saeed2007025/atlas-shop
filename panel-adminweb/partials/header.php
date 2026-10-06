<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../inc/helpers.php';
require_once __DIR__ . '/../../inc/auth.php';

require_admin();
$admin = current_admin();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت اطلس شاپ</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=<?php echo ASSETS_VER; ?>">
</head>
<body>

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-search">
                <input type="text" placeholder="جستجو در پنل مدیریت...">
            </div>

            <div class="topbar-actions">
                <div style="font-size: 13px; color: var(--admin-muted);">
                    امروز: <?php echo to_persian_num(date('Y/m/d')); ?>
                </div>

                <a href="product-create.php" class="btn-green-add">
                    <span>افزودن جدید +</span>
                </a>
            </div>
        </header>

        <div style="padding: 24px;">
