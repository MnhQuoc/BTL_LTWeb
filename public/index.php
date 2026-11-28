<?php
declare(strict_types=1);
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/views/layout.php';
require_once dirname(__DIR__) . '/app/controllers/AppController.php';

$pdo = database();
$controller = new AppController($pdo);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET /' => 'home',
    'GET /about' => 'about',
    'GET /products' => 'products',
    'GET /pricing' => 'pricing',
    'GET /faq' => 'faq',
    'GET /news' => 'news',
    'GET /robots.txt' => 'robots',
    'GET /sitemap.xml' => 'sitemap',
    'GET /cart' => 'cart',
    'GET /checkout' => 'checkout',
    'GET /login' => 'login',
    'GET /register' => 'register',
    'GET /profile' => 'profile',
    'GET /profile/password' => 'changePassword',
    'GET /orders' => 'orderHistory',
    'GET /logout' => 'logout',
    'GET /admin' => 'admin',
    'GET /seller' => 'seller',
    'GET /seller/revenue' => 'sellerRevenue',
    'GET /seller/products/new' => 'sellerProductNew',
    'POST /register' => 'registerPost',
    'POST /login' => 'loginPost',
    'POST /cart/add' => 'cartAdd',
    'POST /cart/update' => 'cartUpdate',
    'POST /checkout' => 'checkoutPost',
    'POST /profile' => 'profilePost',
    'POST /profile/password' => 'changePasswordPost',
    'POST /seller/products/save' => 'sellerProductSave',
    'POST /seller/products/delete' => 'sellerProductDelete',
    'POST /seller/products/availability' => 'sellerProductAvailability',
    'POST /seller/orders/status' => 'sellerOrderItemStatus',
    'POST /news/comment' => 'newsCommentPost',
    'POST /admin/posts/save' => 'adminPostSave',
    'POST /admin/posts/delete' => 'adminPostDelete',
];
$key = $method . ' ' . $path;

if (isset($routes[$key])) {
    $controller->{$routes[$key]}();
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/(users|products|orders|faqs|posts|comments)$#', $path, $matches)) {
    $controller->adminSection($matches[1]);
    exit;
}
if ($method === 'GET' && preg_match('#^/admin/posts/([0-9]+)/edit$#', $path, $matches)) {
    $controller->adminPostEdit((int) $matches[1]);
    exit;
}
if ($method === 'GET' && $path === '/admin/posts/new') {
    $controller->adminPostNew();
    exit;
}

if ($method === 'POST' && $path === '/admin/action') {
    $controller->adminAction();
    exit;
}

if ($method === 'GET' && preg_match('#^/seller/products/([0-9]+)/edit$#', $path, $matches)) {
    $controller->sellerProductEdit((int) $matches[1]);
    exit;
}

if ($method === 'GET' && str_starts_with($path, '/products/')) {
    $controller->productDetail(substr($path, strlen('/products/')));
    exit;
}
if ($method === 'GET' && str_starts_with($path, '/news/')) {
    $controller->newsDetail(substr($path, strlen('/news/')));
    exit;
}
http_response_code(404);
renderLayout('Không tìm thấy trang', dirname(__DIR__) . '/app/views/404.php');
