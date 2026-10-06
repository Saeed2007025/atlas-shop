<?php
/**
 * Torob (ترب) Products Feed API
 * Official Torob Products Integration API Endpoint
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$isTorobActive = get_setting('torob_active', '1') == '1';
$requiredToken = get_setting('torob_token', '');
$providedToken = $_GET['token'] ?? '';

if (!$isTorobActive) {
    json_response(['error' => 'Torob API is currently disabled.'], 403);
}

if (!empty($requiredToken) && !hash_equals($requiredToken, $providedToken)) {
    json_response(['error' => 'Invalid Torob Security Token.'], 401);
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 100;
$offset = ($page - 1) * $limit;

$db = get_db();
if (!$db) {
    json_response(['error' => 'Database connection error.'], 500);
}

try {
    $totalCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    $limit = (int)$limit;
    $offset = (int)$offset;
    $stmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT $limit OFFSET $offset");
    $products = $stmt->fetchAll();

    $productsList = [];
    foreach ($products as $p) {
        $effectivePrice = ($p['discount_price'] > 0) ? $p['discount_price'] : $p['price'];
        $oldPrice = ($p['discount_price'] > 0) ? $p['price'] : 0;

        $imageUrl = $p['image'] ? (strpos($p['image'], 'http') === 0 ? $p['image'] : SITE_URL . '/' . ltrim($p['image'], '/')) : SITE_URL . '/assets/images/cat-gadgets.webp';

        $productsList[] = [
            'product_id' => $p['id'],
            'title' => $p['name'],
            'category_name' => $p['category_name'] ?: 'لوازم جانبی',
            'brand' => $p['brand'] ?: 'متفرقه',
            'price' => (int)$effectivePrice,
            'old_price' => (int)$oldPrice,
            'availability' => ($p['stock'] > 0) ? 'instock' : 'out_of_stock',
            'stock' => (int)$p['stock'],
            'page_url' => SITE_URL . '/product.php?id=' . $p['id'],
            'image_url' => $imageUrl
        ];
    }

    json_response([
        'count' => count($productsList),
        'total_count' => (int)$totalCount,
        'page' => $page,
        'total_pages' => $totalPages,
        'products' => $productsList
    ]);

} catch (Exception $e) {
    json_response(['error' => 'Error generating Torob feed.'], 500);
}
