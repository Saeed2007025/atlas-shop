<?php
/**
 * Digikala (دیجی‌کالا) Seller Center Feed API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$isDigikalaActive = get_setting('digikala_active', '1') == '1';
$sellerId = get_setting('digikala_seller_id', '');

if (!$isDigikalaActive) {
    json_response(['error' => 'Digikala Module is currently disabled.'], 403);
}

$db = get_db();
if (!$db) {
    json_response(['error' => 'Database connection error.'], 500);
}

try {
    $stmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    $products = $stmt->fetchAll();

    $exportList = [];
    foreach ($products as $p) {
        $effectivePrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price'];
        $imageUrl = $p['image'] ? (strpos($p['image'], 'http') === 0 ? $p['image'] : SITE_URL . '/' . ltrim($p['image'], '/')) : SITE_URL . '/assets/images/cat-gadgets.webp';

        $exportList[] = [
            'sku' => 'ATL-SKU-' . $p['id'],
            'title_fa' => $p['name'],
            'category_title' => $p['category_name'],
            'brand_fa' => $p['brand'] ?: 'متفرقه',
            'selling_price' => (int)($effectivePrice * 10), // Digikala Rial conversion
            'rrp_price' => (int)($p['price'] * 10),
            'stock_status' => ($p['stock'] > 0) ? 'in_stock' : 'out_of_stock',
            'quantity' => (int)$p['stock'],
            'main_image' => $imageUrl,
            'url' => SITE_URL . '/product.php?id=' . $p['id']
        ];
    }

    json_response([
        'seller_id' => $sellerId ?: 'ATL-SELLER-001',
        'generated_at' => date('Y-m-d H:i:s'),
        'total_items' => count($exportList),
        'products' => $exportList
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error generating Digikala feed.'], 500);
}
