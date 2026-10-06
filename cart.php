<?php
/**
 * Shopping Cart Page
 * Clean Light Palette matching Homepage (#2F67E8 Blue + White)
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/seo.php';

$activePage = 'cart';
$isDarkTheme = false; // Clean light theme matching homepage palette!
$seoParams = [
    'title' => 'سبد خرید و سفارش‌های شما',
    'description' => 'مشاهده و مدیریت اقلام موجود در سبد خرید و پیگیری سفارش‌های قبلی شما.'
];

$db = get_db();
$customer = current_customer();

// Calculate cart items from session
$cartItems = [];
$subtotal = 0;
$totalCount = 0;

if (!empty($_SESSION['cart']) && $db) {
    $ids = array_keys($_SESSION['cart']);
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $db->prepare("SELECT id, name, price, discount_price, image, stock FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $dbProducts = $stmt->fetchAll();

    foreach ($dbProducts as $p) {
        $id = $p['id'];
        $qty = (int)($_SESSION['cart'][$id] ?? 1);
        $effPrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price'];
        $itemTotal = $effPrice * $qty;

        $subtotal += $itemTotal;
        $totalCount += $qty;

        $cartItems[] = [
            'id' => $id,
            'name' => $p['name'],
            'price' => $effPrice,
            'original_price' => $p['price'],
            'qty' => $qty,
            'stock' => $p['stock'],
            'image' => $p['image'],
            'item_total' => $itemTotal
        ];
    }
}

// Fetch Customer's Previous Orders if logged in
$previousOrders = [];
if ($customer && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? OR receiver_mobile = ? ORDER BY id DESC LIMIT 10");
        $stmt->execute([$customer['id'], $customer['mobile']]);
        $previousOrders = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Recommended Products
$recommended = [];
if ($db) {
    $recommended = $db->query("SELECT * FROM products ORDER BY rating DESC LIMIT 5")->fetchAll();
}

require_once __DIR__ . '/inc/header.php';
?>

<div class="container" style="margin-top: 24px;">

    <!-- Breadcrumb & Header Title -->
    <div style="font-size: 13px; color: var(--muted); margin-bottom: 8px;">
        <a href="<?php echo SITE_URL; ?>/">خانه</a> &gt; <span>سبد خرید</span>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h1 style="font-size: 20px; font-weight: 800; color: var(--text);">
            سبد خرید شما <span style="font-size: 13px; font-weight: normal; color: var(--muted);">(<?php echo to_persian_num($totalCount); ?> کالا)</span>
        </h1>

        <?php if (!empty($cartItems)): ?>
            <button type="button" onclick="if(confirm('آیا از حذف تمام کالاهای سبد خرید اطمینان دارید؟')){ fetch('api/cart.php?action=clear',{method:'POST'}).then(()=>location.reload()); }" style="background: none; border: none; color: var(--danger); font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>حذف همه</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($cartItems)): ?>
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 50px 20px; text-align: center; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--muted); margin-bottom: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <h2 style="font-size: 18px; font-weight: bold; color: var(--text);">سبد خرید شما در حال حاضر خالی است!</h2>
            <p style="color: var(--muted); margin: 12px 0 24px 0;">می‌توانید برای مشاهده محصولات جدید به صفحه فروشگاه مراجعه کنید.</p>
            <a href="shop" class="action-btn btn-login" style="display: inline-flex; padding: 12px 28px;">بازگشت به فروشگاه</a>
        </div>
    <?php else: ?>

        <div class="cart-page-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 40px;">

            <!-- Left Column: Items Table -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--shadow-sm); overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 320px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); color: var(--muted); font-size: 12px; text-align: right;">
                            <th style="padding: 10px 6px;">محصول</th>
                            <th style="padding: 10px 6px;">قیمت واحد</th>
                            <th style="padding: 10px 6px; text-align: center;">تعداد</th>
                            <th style="padding: 10px 6px; text-align: left;">جمع کل</th>
                            <th style="padding: 10px 4px; width: 30px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px 6px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" style="width: 50px; height: 50px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border); background: #FFFFFF; flex-shrink: 0;">
                                        <div>
                                            <a href="product?id=<?php echo $item['id']; ?>" style="font-weight: bold; font-size: 13px; color: var(--text); display: block; line-height: 1.3;"><?php echo e($item['name']); ?></a>
                                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">اصلی با گارانتی</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 6px; font-weight: bold; font-size: 13px; color: var(--text); white-space: nowrap;">
                                    <?php echo format_price($item['price']); ?>
                                </td>
                                <td style="padding: 12px 6px; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--surface-soft);">
                                        <button type="button" onclick="updateCartQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] - 1; ?>)" style="padding: 4px 8px; background: none; border: none; cursor: pointer; color: var(--text); font-weight: bold;">-</button>
                                        <span style="padding: 4px 8px; font-weight: bold; font-size: 13px; color: var(--text);"><?php echo to_persian_num($item['qty']); ?></span>
                                        <button type="button" onclick="updateCartQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] + 1; ?>)" style="padding: 4px 8px; background: none; border: none; cursor: pointer; color: var(--text); font-weight: bold;">+</button>
                                    </div>
                                </td>
                                <td style="padding: 12px 6px; font-weight: 800; font-size: 14px; color: var(--primary); text-align: left; white-space: nowrap;">
                                    <?php echo format_price($item['item_total']); ?>
                                </td>
                                <td style="padding: 12px 4px; text-align: center;">
                                    <button type="button" onclick="removeItemFromCart(<?php echo $item['id']; ?>)" style="background: none; border: none; color: var(--danger); font-size: 20px; cursor: pointer;" title="حذف کالا">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right Column: Order Summary Sidebar -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; height: fit-content; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px; color: var(--primary);">خلاصه سفارش</h3>

                <div style="display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 14px;">
                    <span style="color: var(--muted);">جمع کل کالاها (<?php echo to_persian_num($totalCount); ?>)</span>
                    <span style="font-weight: bold; color: var(--text);"><?php echo format_price($subtotal); ?></span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 18px; font-weight: 800;">
                    <span style="color: var(--text);">مبلغ قابل پرداخت</span>
                    <span style="color: var(--primary);"><?php echo format_price($subtotal); ?></span>
                </div>

                <a href="checkout" class="action-btn btn-login" style="text-align: center; justify-content: center; font-size: 16px; font-weight: 800; width: 100%; margin-top: 24px; padding: 14px;">
                    ادامه و ورود به تسویه‌حساب
                </a>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 12px; color: var(--muted); display: flex; flex-direction: column; gap: 8px;">
                    <div>✔ ارسال رایگان برای خریدهای بالای ۱,۰۰۰,۰۰۰ تومان</div>
                    <div>✔ امکان انتخاب نحوه ارسال و کد تخفیف در مرحله بعد</div>
                    <div>✔ ۷ روز ضمانت بازگشت و پشتیبانی ۲۴/۷</div>
                </div>
            </div>

        </div>

    <?php endif; ?>

    <!-- Customer's Previous Orders Section (With id="previous-orders" target anchor) -->
    <?php if ($customer && !empty($previousOrders)): ?>
        <div id="previous-orders" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>سفارش‌های قبلی شما (<?php echo e($customer['full_name'] ?: $customer['mobile']); ?>)</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($previousOrders as $po): ?>
                    <div style="background: var(--surface-soft); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <strong style="font-size: 14px; color: var(--text);">شماره سفارش: <code>#<?php echo e($po['order_number']); ?></code></strong>
                                <span style="font-size: 12px; color: var(--muted); margin-right: 12px;"><?php echo to_persian_num(date('Y/m/d', strtotime($po['created_at']))); ?></span>
                            </div>
                            <div>
                                <?php if ($po['payment_status'] === 'paid'): ?>
                                    <span style="background: rgba(45, 154, 91, 0.15); color: var(--success); padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">پرداخت شده</span>
                                <?php else: ?>
                                    <span style="background: rgba(245, 158, 11, 0.15); color: #F59E0B; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">در انتظار پرداخت</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size: 13px; color: var(--text); display: flex; justify-content: space-between; align-items: center;">
                            <span>مبلغ کل: <strong><?php echo format_price($po['total_amount']); ?></strong></span>
                            <a href="success?order_id=<?php echo $po['id']; ?>" style="color: var(--primary); font-weight: bold;">مشاهده فاکتور ↗</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recommended Slider ("شاید به این‌ها هم نیاز داشته باشید") -->
    <?php if (!empty($recommended)): ?>
        <section style="margin-bottom: 40px;">
            <div class="section-header">
                <h2 class="section-title">شاید به این‌ها هم نیاز داشته باشید</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($recommended as $rp): ?>
                    <?php $effP = ($rp['discount_price'] > 0) ? $rp['discount_price'] : $rp['price']; ?>
                    <div class="product-card">
                        <a href="product?id=<?php echo $rp['id']; ?>" class="product-card-link">
                            <div class="product-image-wrap">
                                <img src="<?php echo e($rp['image']); ?>" alt="<?php echo e($rp['name']); ?>" loading="lazy">
                            </div>
                            <div class="product-title"><?php echo e($rp['name']); ?></div>
                            <div class="product-price-box">
                                <span class="product-current-price"><?php echo format_price($effP); ?></span>
                            </div>
                        </a>
                        <div class="product-card-footer">
                            <button type="button" onclick="addToCart(<?php echo $rp['id']; ?>)" class="btn-add-cart-icon" style="width:100%; border-radius:var(--radius); height:34px; font-size:12px; gap:4px;">
                                افزودن به سبد
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<style>
@media (max-width: 768px) {
    .cart-page-layout {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
}
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
