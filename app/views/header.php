<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageTitle) ?> - FoodStore, món ăn ngon giao tận nơi">
    <title><?= e($pageTitle) ?> | FoodStore</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="Trang chủ FoodStore">Food<span>Store</span></a>
        <button class="menu-toggle" type="button" aria-label="Mở menu" aria-expanded="false">☰</button>
        <nav class="main-nav" aria-label="Điều hướng chính">
            <a href="/">Trang chủ</a>
            <a href="/about">Giới thiệu</a>
            <a href="/products">Sản phẩm</a>
            <a href="/pricing">Bảng giá</a>
            <a href="/news">Tin tức</a>
            <a href="/faq">Hỏi đáp</a>
            <a href="/contact">Liên hệ</a>
        </nav>
        <div class="header-actions">
            <?php if ($currentUser): ?>
                <a href="/profile"><?= e($currentUser['name']) ?></a>
                <?php if ($currentUser['role'] === 'admin'): ?><a href="/admin">Quản trị</a><?php endif; ?>
                <?php if ($currentUser['role'] === 'seller'): ?><a href="/seller">Khu vực người bán</a><?php endif; ?>
                <a href="/logout">Đăng xuất</a>
            <?php else: ?>
                <a href="/login">Đăng nhập</a>
            <?php endif; ?>
            <a class="cart-link" href="/cart">Giỏ hàng <span><?= cartCount() ?></span></a>
        </div>
    </div>
</header>
<main>
