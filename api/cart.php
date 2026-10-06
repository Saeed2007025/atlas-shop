<?php
/**
 * Cart API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_REQUEST['action'] ?? 'get';

if ($action === 'get') {
    $cartData = calculate_cart();
    json_response(['success' => true, 'cart' => $cartData]);
}

if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$productId) {
        json_response(['success' => false, 'message' => 'شناسه محصول نامعتبر است.'], 400);
    }

    $db = get_db();
    $stmt = $db->prepare("SELECT id, name, price, discount_price, stock, image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        json_response(['success' => false, 'message' => 'محصول یافت نشد.'], 404);
    }

    if ($product['stock'] < 1) {
        json_response(['success' => false, 'message' => 'متأسفانه این محصول در حال حاضر موجود نیست.'], 400);
    }

    $currentQty = $_SESSION['cart'][$productId] ?? 0;
    $newQty = $currentQty + $qty;

    if ($newQty > $product['stock']) {
        json_response(['success' => false, 'message' => 'حداکثر موجودی قابل سفارش این محصول ' . $product['stock'] . ' عدد می‌باشد.'], 400);
    }

    $_SESSION['cart'][$productId] = $newQty;
    $cartData = calculate_cart();

    json_response([
        'success' => true,
        'message' => 'محصول با موفقیت به سبد خرید اضافه شد.',
        'cart' => $cartData
    ]);
}

if ($action === 'update') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);

    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }

    $cartData = calculate_cart();
    json_response(['success' => true, 'cart' => $cartData]);
}

if ($action === 'remove') {
    $productId = (int)($_POST['product_id'] ?? 0);
    unset($_SESSION['cart'][$productId]);
    $cartData = calculate_cart();
    json_response(['success' => true, 'cart' => $cartData]);
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    unset($_SESSION['coupon']);
    $cartData = calculate_cart();
    json_response(['success' => true, 'cart' => $cartData]);
}

function calculate_cart() {
    $db = get_db();
    $items = [];
    $totalCount = 0;
    $subtotal = 0;

    if (!empty($_SESSION['cart']) && $db) {
        $ids = array_keys($_SESSION['cart']);
        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $db->prepare("SELECT id, name, price, discount_price, image, stock FROM products WHERE id IN ($in)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $id = $p['id'];
            $qty = (int)($_SESSION['cart'][$id] ?? 1);
            $effectivePrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price'];
            $itemTotal = $effectivePrice * $qty;

            $subtotal += $itemTotal;
            $totalCount += $qty;

            $items[] = [
                'product_id' => $id,
                'name' => $p['name'],
                'price' => $effectivePrice,
                'original_price' => $p['price'],
                'qty' => $qty,
                'stock' => $p['stock'],
                'image' => $p['image'] ? (strpos($p['image'], 'http') === 0 ? $p['image'] : SITE_URL . '/' . ltrim($p['image'], '/')) : SITE_URL . '/assets/images/cat-gadgets.webp',
                'item_total' => $itemTotal,
                'formatted_price' => format_price($effectivePrice),
                'formatted_item_total' => format_price($itemTotal)
            ];
        }
    }

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
    $finalTotal = max(0, $subtotal - $discountAmount);

    return [
        'items' => $items,
        'total_count' => $totalCount,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'final_total' => $finalTotal,
        'formatted_subtotal' => format_price($subtotal),
        'formatted_discount' => format_price($discountAmount),
        'formatted_total' => format_price($finalTotal)
    ];
}
