<?php
/**
 * Dynamic XML Sitemap Generator for Google / Bing Search Engines
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo SITE_URL; ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/shop</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/categories</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/blog</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/about</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/contact</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/faq</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/returns</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

    <?php
    $db = get_db();
    if ($db) {
        try {
            // Categories
            $cats = $db->query("SELECT slug FROM categories WHERE is_active = 1")->fetchAll();
            foreach ($cats as $c) {
                echo "    <url>\n";
                echo "        <loc>" . SITE_URL . "/shop?category=" . htmlspecialchars($c['slug']) . "</loc>\n";
                echo "        <changefreq>weekly</changefreq>\n";
                echo "        <priority>0.8</priority>\n";
                echo "    </url>\n";
            }

            // Products
            $products = $db->query("SELECT id, created_at FROM products")->fetchAll();
            foreach ($products as $p) {
                $lastmod = date('Y-m-d', strtotime($p['created_at'] ?? 'now'));
                echo "    <url>\n";
                echo "        <loc>" . SITE_URL . "/product?id=" . $p['id'] . "</loc>\n";
                echo "        <lastmod>" . $lastmod . "</lastmod>\n";
                echo "        <changefreq>weekly</changefreq>\n";
                echo "        <priority>0.8</priority>\n";
                echo "    </url>\n";
            }

            // Blog Posts
            $posts = $db->query("SELECT slug, created_at FROM posts WHERE status = 'published'")->fetchAll();
            foreach ($posts as $post) {
                $lastmod = date('Y-m-d', strtotime($post['created_at'] ?? 'now'));
                echo "    <url>\n";
                echo "        <loc>" . SITE_URL . "/post?slug=" . htmlspecialchars($post['slug']) . "</loc>\n";
                echo "        <lastmod>" . $lastmod . "</lastmod>\n";
                echo "        <changefreq>monthly</changefreq>\n";
                echo "        <priority>0.7</priority>\n";
                echo "    </url>\n";
            }
        } catch (Exception $e) {}
    }
    ?>
</urlset>
