<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

$seoParams = ['title' => 'شرایط و قوانین و رویه بازگشت کالا'];
require_once __DIR__ . '/inc/header.php';
?>
<div class="container" style="max-width: 800px; margin: 40px auto;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; line-height: 2;">
        <h1 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 16px;">رویه ضمانت و ۷ روز بازگشت کالا</h1>
        <p>در صورتی که کالای خریداری شده دارای اشکال فنی یا مغایرت باشد، تا ۷ روز پس از دریافت می‌توانید موضوع را به پشتیبانی اطلاع دهید.</p>
        <p>کالا باید در جعبه اصلی خود و بدون آسیب‌دیدگی ظاهری باشد.</p>
    </div>
</div>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
