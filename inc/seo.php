<?php
/**
 * SEO Generator Module
 */

require_once __DIR__ . '/helpers.php';

function render_seo_tags($params = []) {
    $siteName = get_setting('site_name', 'اطلس شاپ');
    $title = !empty($params['title']) ? e($params['title']) . ' | ' . e($siteName) : e($siteName) . ' - فروشگاه تخصصی لوازم جانبی موبایل و گجت';
    $description = !empty($params['description']) ? e($params['description']) : 'فروشگاه اینترنتی اطلس شاپ - مرجع خرید انواع شارژر، کابل، هندزفری، پاوربانک و گجت‌های هوشمند اصلی با بهترین قیمت و ضمانت اصالت.';
    $canonical = !empty($params['canonical']) ? e($params['canonical']) : e(SITE_URL . strtok($_SERVER['REQUEST_URI'], '?'));
    $image = !empty($params['image']) ? e($params['image']) : SITE_URL . '/assets/images/logo.png';
    $type = !empty($params['type']) ? e($params['type']) : 'website';

    $html = '';
    $html .= '<title>' . $title . '</title>' . "\n";
    $html .= '<meta name="description" content="' . $description . '">' . "\n";
    $html .= '<link rel="canonical" href="' . $canonical . '">' . "\n";

    // OpenGraph
    $html .= '<meta property="og:site_name" content="' . e($siteName) . '">' . "\n";
    $html .= '<meta property="og:type" content="' . $type . '">' . "\n";
    $html .= '<meta property="og:title" content="' . $title . '">' . "\n";
    $html .= '<meta property="og:description" content="' . $description . '">' . "\n";
    $html .= '<meta property="og:url" content="' . $canonical . '">' . "\n";
    $html .= '<meta property="og:image" content="' . $image . '">' . "\n";

    // Twitter Card
    $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $html .= '<meta name="twitter:title" content="' . $title . '">' . "\n";
    $html .= '<meta name="twitter:description" content="' . $description . '">' . "\n";
    $html .= '<meta name="twitter:image" content="' . $image . '">' . "\n";

    // Product Schema JSON-LD if product page
    if ($type === 'product' && !empty($params['product'])) {
        $p = $params['product'];
        $price = $p['discount_price'] ? $p['discount_price'] : $p['price'];
        $schema = [
            "@context" => "https://schema.org/",
            "@type" => "Product",
            "name" => $p['name'],
            "image" => [$p['image'] ? (strpos($p['image'], 'http') === 0 ? $p['image'] : SITE_URL . '/' . ltrim($p['image'], '/')) : $image],
            "description" => mb_substr(strip_tags($p['description'] ?? $p['name']), 0, 160, 'UTF-8'),
            "brand" => [
                "@type" => "Brand",
                "name" => $p['brand'] ?? $siteName
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => $canonical,
                "priceCurrency" => "IRR",
                "price" => $price,
                "availability" => ($p['stock'] > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                "itemCondition" => "https://schema.org/NewCondition"
            ]
        ];
        if (!empty($p['rating'])) {
            $schema["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingValue" => $p['rating'],
                "reviewCount" => max(1, (int)($p['reviews_count'] ?? 1))
            ];
        }
        $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    // Article Schema JSON-LD if blog post
    if ($type === 'article' && !empty($params['post'])) {
        $post = $params['post'];
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $post['title'],
            "image" => [$post['image'] ? SITE_URL . '/' . ltrim($post['image'], '/') : $image],
            "datePublished" => $post['created_at'],
            "author" => [
                "@type" => "Person",
                "name" => $post['author'] ?? 'تیم تحریریه اطلس شاپ'
            ]
        ];
        $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    return $html;
}

function seo_breadcrumb_schema($items) {
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'] ?? null
        ];
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

function seo_organization_schema() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'اطلس شاپ',
        'url' => 'https://atlashop.ir',
        'logo' => 'https://atlashop.ir/assets/images/logo.webp',
        'description' => 'فروشگاه اینترنتی لوازم جانبی موبایل و تبلت',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+98-912-000-0000',
            'contactType' => 'customer service',
            'areaServed' => 'IR',
            'availableLanguage' => 'Persian'
        ],
        'sameAs' => [
            'https://instagram.com/atlashop.ir'
        ]
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

function seo_website_schema() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'اطلس شاپ',
        'url' => 'https://atlashop.ir',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => 'https://atlashop.ir/shop?search={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ]
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

function seo_faq_schema($faqs) {
    $items = [];
    foreach ($faqs as $faq) {
        $items[] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer']
            ]
        ];
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $items
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
