<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$sellerArea = $currentUser && $currentUser['role'] === 'seller' &&
    ($requestPath === '/seller' || str_starts_with((string) $requestPath, '/seller/'));
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = $scheme . '://' . $host . $requestPath;
$seoDescription = $seoDescription ?? match ($requestPath) {
    '/' => 'FoodStore - đặt món ngon, giao nhanh và minh bạch nguồn gốc nguyên liệu.',
    '/about' => 'Tìm hiểu FoodStore, sứ mệnh mang hương vị Việt đến tận bàn ăn.',
    '/products' => 'Khám phá thực đơn món ngon và đặt hàng tại FoodStore.',
    '/pricing' => 'Xem bảng giá các món ăn tại FoodStore.',
    '/faq' => 'Câu hỏi thường gặp về đặt hàng, thanh toán và giao hàng tại FoodStore.',
    '/news' => 'Tin tức, mẹo chọn thực phẩm và bài viết mới nhất từ FoodStore.',
    default => 'FoodStore - món ăn ngon, giao nhanh tại nội thành.',
};
$seoImage = $seoImage ?? '/assets/images/logo.avif';
if (!str_starts_with($seoImage, 'http')) {
    $seoImage = $scheme . '://' . $host . $seoImage;
}
$seoType = $seoType ?? 'website';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'FoodStore',
    'url' => $scheme . '://' . $host,
    'logo' => $scheme . '://' . $host . '/assets/images/logo.avif',
];
if (isset($product)) {
    $seoDescription = $product['short_description'] ?: 'Thông tin sản phẩm ' . $product['name'] . ' tại FoodStore.';
    $seoImage = $product['image'] ?: '/assets/images/logo.avif';
    $seoType = 'product';
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'description' => $seoDescription,
        'image' => str_starts_with((string) $seoImage, 'http') ? $seoImage : $scheme . '://' . $host . $seoImage,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'VND',
            'price' => (int) $product['price'],
            'availability' => (int) $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => $canonicalUrl,
        ],
    ];
}
if (isset($post)) {
    $seoDescription = $post['excerpt'] ?: mb_substr(strip_tags($post['content']), 0, 160);
    $seoImage = $post['image'] ?: '/assets/images/logo.avif';
    $seoType = 'article';
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post['title'],
        'description' => $seoDescription,
        'image' => str_starts_with((string) $seoImage, 'http') ? $seoImage : $scheme . '://' . $host . $seoImage,
        'datePublished' => $post['published_at'] ?? $post['created_at'],
        'dateModified' => $post['updated_at'] ?? ($post['published_at'] ?? $post['created_at']),
        'author' => ['@type' => 'Person', 'name' => $post['author_name'] ?? 'FoodStore'],
        'mainEntityOfPage' => $canonicalUrl,
    ];
}
if (!str_starts_with((string) $seoImage, 'http')) {
    $seoImage = $scheme . '://' . $host . $seoImage;
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($seoDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:type" content="<?= e($seoType) ?>">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:site_name" content="FoodStore">
    <meta property="og:title" content="<?= e($pageTitle) ?> | FoodStore">
    <meta property="og:description" content="<?= e($seoDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($seoImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?> | FoodStore">
    <meta name="twitter:description" content="<?= e($seoDescription) ?>">
    <meta name="twitter:image" content="<?= e($seoImage) ?>">
    <title><?= e($pageTitle) ?> | FoodStore</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="Trang chủ FoodStore"><img src="/assets/images/logo.avif" alt=""><span class="brand-name">Food<span>Store</span></span></a>
        <button class="menu-toggle" type="button" aria-label="Mở menu" aria-expanded="false">☰</button>
        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <nav class="main-nav admin-nav" aria-label="Menu quản trị">
                <a href="/admin">Tổng quan</a>
                <a href="/admin/users">Thành viên</a>
                <a href="/admin/products">Sản phẩm</a>
                <a href="/admin/orders">Đơn hàng</a>
                <a href="/admin/faqs">FAQ</a>
                <a href="/admin/posts">Bài viết</a>
                <a href="/admin/comments">Bình luận</a>
            </nav>
        <?php elseif ($sellerArea): ?>
            <nav class="main-nav seller-nav" aria-label="Menu người bán">
                <a href="/seller">Tổng quan</a>
                <a href="/seller#orders">Đơn hàng</a>
                <a href="/seller#products">Thực đơn</a>
                <a href="/seller/revenue">Doanh thu</a>
                <details class="seller-account">
                    <summary><?= e($currentUser['name']) ?></summary>
                    <div class="seller-account-links">
                        <a href="/profile">Hồ sơ quán</a>
                        <a href="/">Về trang mua hàng</a>
                        <a href="/logout" data-confirm="Bạn có chắc chắn muốn đăng xuất không?">Đăng xuất</a>
                    </div>
                </details>
            </nav>
        <?php elseif ($currentUser && $currentUser['role'] === 'user'): ?>
            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="/">Trang chủ</a>
                <a href="/about">Giới thiệu</a>
                <a href="/products">Sản phẩm</a>
                <a href="/faq">Hỏi đáp</a>
                <a href="/news">Tin tức</a>
            </nav>
        <?php else: ?>
            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="/">Trang chủ</a>
                <a href="/about">Giới thiệu</a>
                <a href="/products">Sản phẩm</a>
                <a href="/faq">Hỏi đáp</a>
                <a href="/news">Tin tức</a>
            </nav>
        <?php endif; ?>
        <div class="header-actions">
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <details class="admin-account-menu">
                    <summary class="admin-label">FoodStore Admin</summary>
                    <div class="admin-account-links">
                        <a href="/logout" data-confirm="Bạn có chắc chắn muốn đăng xuất không?">Đăng xuất</a>
                    </div>
                </details>
            <?php elseif ($currentUser && $currentUser['role'] === 'user'): ?>
                <details class="user-account-menu">
                    <summary class="user-label"><?= e($currentUser['name']) ?></summary>
                    <div class="user-account-links">
                        <a href="/profile">Hồ sơ</a>
                        <a href="/orders">Lịch sử mua hàng</a>
                        <a href="/logout" data-confirm="Bạn có chắc chắn muốn đăng xuất không?">Đăng xuất</a>
                    </div>
                </details>
            <?php elseif ($currentUser && !$sellerArea): ?>
                <a href="/profile"><?= e($currentUser['name']) ?></a>
                <?php if ($currentUser['role'] === 'seller'): ?><a href="/seller">Khu vực người bán</a><?php endif; ?>
                <a href="/logout" data-confirm="Bạn có chắc chắn muốn đăng xuất không?">Đăng xuất</a>
            <?php elseif (!$currentUser): ?>
                <a href="/login">Đăng nhập</a>
            <?php endif; ?>
            <?php if (!$currentUser || ($currentUser['role'] !== 'admin' && !$sellerArea)): ?>
                <a class="cart-link" href="/cart">Giỏ hàng <span><?= cartCount() ?></span></a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
