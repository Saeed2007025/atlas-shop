<?php
/**
 * Checkout & Order Completion Page
 * Clean Light Theme Palette (#2F67E8 Blue + White) matching Homepage
 * Structure matching Reference Mockup 5
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/zarinpal.php';

$activePage = 'checkout';
$isDarkTheme = false; // Clean light theme matching homepage palette!
$seoParams = [
    'title' => 'اطلاعات ارسال و تکمیل سفارش',
    'description' => 'تکمیل اطلاعات آدرس ارسال، انتخاب روش دریافت و پرداخت آنلاین امن با درگاه زرین‌پال.'
];

// Guest check: Must be logged in to access checkout
if (!is_customer_logged_in()) {
    redirect(SITE_URL . '/login.php?redirect=checkout.php');
}

$customer = current_customer();
$db = get_db();

// Verify Cart Not Empty
if (empty($_SESSION['cart'])) {
    redirect(SITE_URL . '/cart.php');
}

// Calculate Cart Items
$cartItems = [];
$subtotal = 0;
$totalCount = 0;

$ids = array_keys($_SESSION['cart']);
$in = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $db->prepare("SELECT id, name, price, discount_price, image FROM products WHERE id IN ($in)");
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
        'qty' => $qty,
        'image' => $p['image'],
        'item_total' => $itemTotal
    ];
}

// Discount calculation
$discountAmount = 0;
if (!empty($_SESSION['coupon'])) {
    $cp = $_SESSION['coupon'];
    if ($cp['type'] === 'percent') {
        $discountAmount = ($subtotal * $cp['value']) / 100;
        if (!empty($cp['max_discount']) && $discountAmount > $cp['max_discount']) {
            $discountAmount = $cp['max_discount'];
        }
    } else {
        $discountAmount = $cp['value'];
    }
}
$discountAmount = min($subtotal, $discountAmount);

// Fetch Shipping Methods from Database
$shippingMethods = $db->query("SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY display_order ASC, id ASC")->fetchAll();

if (empty($shippingMethods)) {
    $db->prepare("INSERT INTO shipping_methods (title, code, description, cost, free_threshold, allowed_provinces, display_order) VALUES ('ارسال پستی پیشتاز', 'post', 'تحویل در ۲ تا ۴ روز کاری به سراسر کشور', 50000, 1000000, 'all', 1)")->execute();
    $db->prepare("INSERT INTO shipping_methods (title, code, description, cost, free_threshold, allowed_provinces, display_order) VALUES ('پیک موتوری شهری (ویژه قم)', 'courier', 'تحویل سریع در همان روز (ویژه شهر قم)', 35000, 0, 'قم', 2)")->execute();
    $db->prepare("INSERT INTO shipping_methods (title, code, description, cost, free_threshold, allowed_provinces, display_order) VALUES ('پیک سریع اکسپرس (ویژه قم)', 'courier_express', 'تحویل کمتر از ۲ ساعت (ویژه شهر قم)', 55000, 0, 'قم', 3)")->execute();
    
    $shippingMethods = $db->query("SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY display_order ASC, id ASC")->fetchAll();
}

$selectedMethodCode = $_POST['shipping_method'] ?? $shippingMethods[0]['code'];
$selectedMethodCost = 0;

foreach ($shippingMethods as $sm) {
    if ($sm['code'] === $selectedMethodCode) {
        if ($sm['free_threshold'] > 0 && $subtotal >= $sm['free_threshold']) {
            $selectedMethodCost = 0;
        } else {
            $selectedMethodCost = $sm['cost'];
        }
        break;
    }
}

$payableTotal = max(0, $subtotal - $discountAmount + $selectedMethodCost);

// Read Iran Cities JSON File directly in PHP for 100% instant reliability
$citiesJsonRaw = file_get_contents(PATH_ROOT . '/data/iran-cities.json');
$provincesData = json_decode($citiesJsonRaw, true) ?: [];

// Order Submission Processing
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    verify_csrf();
    check_honeypot();

    $receiverName = trim($_POST['receiver_name'] ?? '');
    $receiverMobile = trim($_POST['receiver_mobile'] ?? '');
    $receiverEmail = trim($_POST['receiver_email'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $selectedMethodCode = trim($_POST['shipping_method'] ?? 'post');

    if (empty($receiverName) || empty($receiverMobile) || empty($state) || empty($city) || empty($address)) {
        $errors[] = 'لطفاً تمامی فیلدهای ضروری (نام، موبایل، استان، شهر و آدرس) را تکمیل کنید.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE customers SET full_name = ?, email = ?, state = ?, city = ?, address = ?, postal_code = ? WHERE id = ?");
            $stmt->execute([$receiverName, $receiverEmail, $state, $city, $address, $postalCode, $customer['id']]);

            $orderNumber = 'ATL-' . date('Ymd') . '-' . str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $db->prepare("INSERT INTO orders (order_number, customer_id, receiver_name, receiver_mobile, receiver_email, state, city, district, address, postal_code, shipping_method, shipping_cost, subtotal, discount_amount, total_amount, payment_status, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', NOW())");
            $stmt->execute([
                $orderNumber, $customer['id'], $receiverName, $receiverMobile, $receiverEmail, $state, $city, $district, $address, $postalCode, $selectedMethodCode, $selectedMethodCost, $subtotal, $discountAmount, $payableTotal
            ]);
            $orderId = $db->lastInsertId();

            foreach ($cartItems as $item) {
                 $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
                 $stmt->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty'], $item['item_total']]);
            }

            if (!empty($_SESSION['coupon'])) {
                $couponId = $_SESSION['coupon']['id'];
                $stmt = $db->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = ?");
                $stmt->execute([$couponId]);
                $stmt = $db->prepare("INSERT INTO coupon_usages (customer_id, coupon_id, order_id, used_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$customer['id'], $couponId, $orderId]);
            }

            $callbackUrl = SITE_URL . '/zarinpal-callback.php?order_id=' . $orderId;
            $desc = "پرداخت سفارش شماره $orderNumber در اطلس شاپ";
            
            $zarinpalRes = zarinpal_request($payableTotal, $desc, $callbackUrl, $receiverMobile, $receiverEmail);

            if ($zarinpalRes['success']) {
                $stmt = $db->prepare("INSERT INTO payment_transactions (order_id, authority, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                $stmt->execute([$orderId, $zarinpalRes['authority'], $payableTotal]);

                $db->commit();
                
                $_SESSION['cart'] = [];
                unset($_SESSION['coupon']);

                redirect($zarinpalRes['url']);
            } else {
                $db->rollBack();
                $errors[] = $zarinpalRes['message'] ?? 'خطا هنگام انتقال به درگاه پرداخت.';
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = 'خطایی رخ داد. لطفا دوباره تلاش کنید.';
        }
    }
}

$extraJs = ['assets/js/checkout.js'];
$extraCss = ['assets/css/checkout.css'];
require_once __DIR__ . '/inc/header.php';
?>

<div class="container">

    <!-- Step Progress Indicator (1. سبد خرید, 2. اطلاعات ارسال, 3. روش پرداخت, 4. تکمیل سفارش) -->
    <div class="checkout-steps-bar">
        <div class="step-item completed">
            <span class="step-num">۱</span>
            <span>سبد خرید</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item active">
            <span class="step-num">۲</span>
            <span>اطلاعات ارسال</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">۳</span>
            <span>روش پرداخت</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <span class="step-num">۴</span>
            <span>تکمیل سفارش</span>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div style="background: rgba(215,80,93,0.1); border: 1px solid var(--danger); color: var(--danger); padding: 16px; border-radius: 12px; margin-bottom: 20px; font-weight: bold;">
            <?php foreach ($errors as $err) echo e($err) . '<br>'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="checkout-grid">
        <?php echo csrf_field(); ?>
        <?php echo honeypot_field(); ?>

        <input type="hidden" id="checkout-subtotal-val" value="<?php echo $subtotal; ?>">
        <input type="hidden" id="checkout-discount-val" value="<?php echo $discountAmount; ?>">

        <!-- Right Column: Shipping & Receiver Information Form -->
        <div>
            <!-- Box 1: Receiver Info -->
            <div class="checkout-box">
                <h2 class="checkout-box-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>اطلاعات گیرنده</span>
                </h2>

                <div class="form-group">
                    <label>شماره همراه *</label>
                    <input type="text" name="receiver_mobile" value="<?php echo e($customer['mobile'] ?? ''); ?>" placeholder="مثال: ۰۹۱۲۳۴۵۶۷۸۹" class="form-input" style="direction: ltr; font-weight: bold;" required>
                    <div class="input-verified-badge">
                        <span>✓ کد تایید برای این شماره ارسال خواهد شد.</span>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>نام و نام خانوادگی *</label>
                        <input type="text" name="receiver_name" value="<?php echo e($customer['full_name'] ?? ''); ?>" placeholder="نام و نام خانوادگی تحویل گیرنده" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label>ایمیل (اختیاری)</label>
                        <input type="email" name="receiver_email" value="<?php echo e($customer['email'] ?? ''); ?>" placeholder="ایمیل (اختیاری)" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Box 2: Shipping Address -->
            <div class="checkout-box">
                <h2 class="checkout-box-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>آدرس ارسال</span>
                </h2>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>استان *</label>
                        <?php $selectedState = $customer['state'] ?? 'قم'; ?>
                        <select name="state" id="province-select" data-selected="<?php echo e($selectedState); ?>" class="form-select" required>
                            <option value="">انتخاب استان...</option>
                            <?php foreach ($provincesData as $pItem): ?>
                                <option value="<?php echo e($pItem['province']); ?>" <?php echo $selectedState === $pItem['province'] ? 'selected' : ''; ?>>
                                    <?php echo e($pItem['province']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>شهر *</label>
                        <select name="city" id="city-select" data-selected="<?php echo e($customer['city'] ?? ''); ?>" class="form-select" required>
                            <option value="">انتخاب شهر...</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>منطقه / محله</label>
                    <input type="text" name="district" value="" placeholder="مثال: صفائیه / سعادت آباد" class="form-input">
                </div>

                <div class="form-group">
                    <label>آدرس دقیق *</label>
                    <textarea name="address" class="form-input" rows="2" placeholder="خیابان، پلاک، واحد..." required><?php echo e($customer['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>کد پستی</label>
                    <input type="text" name="postal_code" value="<?php echo e($customer['postal_code'] ?? ''); ?>" placeholder="کد پستی ۱۰ رقمی" maxlength="10" class="form-input" style="direction: ltr;">
                </div>

                <label class="toggle-switch-wrap">
                    <input type="checkbox" name="save_info" value="1" checked style="accent-color: var(--primary);">
                    <span>اطلاعات را برای سفارش‌های بعدی ذخیره کن</span>
                </label>
            </div>

            <!-- Box 3: Shipping Method Selection (Filtered Dynamically by Selected Province) -->
            <div class="checkout-box">
                <h2 class="checkout-box-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>زمان و نحوه ارسال</span>
                </h2>

                <div class="shipping-options-list">
                    <?php foreach ($shippingMethods as $idx => $sm): ?>
                        <?php
                            $costText = format_price($sm['cost']);
                            if ($sm['free_threshold'] > 0 && $subtotal >= $sm['free_threshold']) {
                                $costText = 'رایگان';
                            }
                            $isDefaultChecked = ($idx === 0);
                        ?>
                        <label class="shipping-option-card <?php echo $isDefaultChecked ? 'selected' : ''; ?>" 
                               data-allowed-provinces="<?php echo e($sm['allowed_provinces']); ?>" 
                               data-cost="<?php echo $sm['cost']; ?>" 
                               data-free-threshold="<?php echo $sm['free_threshold']; ?>">
                            <div class="shipping-option-left">
                                <input type="radio" name="shipping_method" value="<?php echo e($sm['code']); ?>" <?php echo $isDefaultChecked ? 'checked' : ''; ?> style="accent-color: var(--primary);">
                                <div>
                                    <div style="font-weight: bold; font-size: 14px; color: var(--text);"><?php echo e($sm['title']); ?></div>
                                    <div style="font-size: 12px; color: var(--muted);"><?php echo e($sm['description']); ?></div>
                                </div>
                            </div>
                            <span class="shipping-cost-badge"><?php echo $costText; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Left Column: Order Summary Box -->
        <div>
            <div class="checkout-summary-box">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px; color: var(--primary);">
                    خلاصه سفارش
                </h3>

                <!-- Cart Items List -->
                <div style="max-height: 240px; overflow-y: auto; margin-bottom: 16px; padding-left: 4px;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; gap: 10px; margin-bottom: 12px; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            <img src="<?php echo e($item['image']); ?>" style="width: 44px; height: 44px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border); background: #FFFFFF;">
                            <div style="flex: 1; font-size: 12px;">
                                <div style="font-weight: bold; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;"><?php echo e($item['name']); ?></div>
                                <div style="color: var(--muted); margin-top: 2px;">رنگ: مشکی</div>
                                <div style="color: var(--primary); font-weight: bold; margin-top: 2px;"><?php echo format_price($item['price']); ?> × <?php echo to_persian_num($item['qty']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Coupon Field -->
                <div style="margin: 16px 0; padding: 12px 0; border-top: 1px dashed var(--border); border-bottom: 1px dashed var(--border);">
                    <div style="font-size: 13px; font-weight: bold; color: var(--text); margin-bottom: 8px;">افزودن کد تخفیف</div>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="coupon-code-input" placeholder="کد تخفیف (مثال: ATLAS10)" class="form-input" style="padding: 8px 12px; font-size: 13px;">
                        <button type="button" id="apply-coupon-btn" class="action-btn btn-login" style="padding: 8px 16px; font-size: 13px;">اعمال</button>
                    </div>
                    <div id="coupon-message" style="margin-top: 6px; font-size: 12px;"></div>
                </div>

                <div class="summary-row">
                    <span>مجموع قیمت کالاها:</span>
                    <span style="font-weight: bold; color: var(--text);"><?php echo format_price($subtotal); ?></span>
                </div>

                <div class="summary-row">
                    <span>هزینه ارسال:</span>
                    <span id="checkout-shipping-cost-display" style="font-weight: bold; color: <?php echo $selectedMethodCost == 0 ? 'var(--success)' : 'var(--text)'; ?>;">
                        <?php echo $selectedMethodCost == 0 ? 'رایگان' : format_price($selectedMethodCost); ?>
                    </span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="summary-row" style="color: var(--danger);">
                        <span>تخفیف:</span>
                        <span style="font-weight: bold;">-<?php echo format_price($discountAmount); ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span>قابل پرداخت:</span>
                    <span id="checkout-payable-total-display"><?php echo format_price($payableTotal); ?></span>
                </div>

                <button type="submit" name="place_order" value="1" class="btn-proceed-pay">
                    ادامه و انتخاب روش پرداخت
                </button>

                <!-- Trust Badges List -->
                <div class="checkout-trust-list">
                    <div style="font-weight: bold; color: var(--text); font-size: 13px; margin-bottom: 4px;">اطمینان از خرید شما</div>

                    <div class="trust-item">
                        <div class="trust-item-icon">✓</div>
                        <div>
                            <div style="font-weight: bold; color: var(--text);">ضمانت اصالت کالا</div>
                            <div style="font-size: 11px; opacity: 0.8;">تمامی محصولات اصل و اورجینال</div>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-item-icon">↺</div>
                        <div>
                            <div style="font-weight: bold; color: var(--text);">۷ روز ضمانت بازگشت</div>
                            <div style="font-size: 11px; opacity: 0.8;">در صورت عدم رضایت</div>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-item-icon">🎧</div>
                        <div>
                            <div style="font-weight: bold; color: var(--text);">پشتیبانی ۲۴/۷</div>
                            <div style="font-size: 11px; opacity: 0.8;">پاسخگوی شما هستیم</div>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-item-icon">🔒</div>
                        <div>
                            <div style="font-weight: bold; color: var(--text);">پرداخت امن</div>
                            <div style="font-size: 11px; opacity: 0.8;">اطلاعات شما محفوظ و امن است</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>

</div>

<script>
window.iranCitiesData = <?php echo $citiesJsonRaw; ?>;
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
