<?php
/**
 * Products API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $q = trim($_GET['q'] ?? '');
    if (mb_strlen($q, 'UTF-8') < 2) {
        json_response(['success' => true, 'products' => []]);
    }

    $db = get_db();
    if (!$db) json_response(['success' => false, 'message' => 'DB error'], 500);

    $stmt = $db->prepare("SELECT id, name, price, discount_price, image FROM products WHERE name LIKE ? OR brand LIKE ? LIMIT 6");
    $searchTerm = "%$q%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $rows = $stmt->fetchAll();

    $products = [];
    foreach ($rows as $r) {
        $effectivePrice = ($r['discount_price'] > 0) ? $r['discount_price'] : $r['price'];
        $products[] = [
            'id' => $r['id'],
            'name' => $r['name'],
            'image' => $r['image'] ? (strpos($r['image'], 'http') === 0 ? $r['image'] : SITE_URL . '/' . ltrim($r['image'], '/')) : SITE_URL . '/assets/images/cat-gadgets.webp',
            'formatted_price' => format_price($effectivePrice)
        ];
    }

    json_response(['success' => true, 'products' => $products]);
}

json_response(['success' => false, 'message' => 'Action invalid'], 400);
