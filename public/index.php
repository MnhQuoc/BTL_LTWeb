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
    'GET /news' => 'news',
    'GET /faq' => 'faq',
    'GET /contact' => 'contact',
    'GET /cart' => 'cart',
    'GET /checkout' => 'checkout',
    'GET /login' => 'login',
    'GET /register' => 'register',
    'GET /profile' => 'profile',
    'GET /logout' => 'logout',
    'GET /admin' => 'admin',
    'GET /seller' => 'seller',
    'GET /seller/products/new' => 'sellerProductNew',
    'POST /contact' => 'contactPost',
    'POST /register' => 'registerPost',
    'POST /login' => 'loginPost',
    'POST /cart/add' => 'cartAdd',
    'POST /cart/update' => 'cartUpdate',
    'POST /checkout' => 'checkoutPost',
    'POST /profile' => 'profilePost',
    'POST /seller/products/save' => 'sellerProductSave',
    'POST /seller/products/delete' => 'sellerProductDelete',
    'POST /seller/orders/status' => 'sellerOrderItemStatus',
];
$key = $method . ' ' . $path;

if (isset($routes[$key])) {
    $controller->{$routes[$key]}();
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
    $controller->postDetail(substr($path, strlen('/news/')));
    exit;
}
http_response_code(404);
renderLayout('Không tìm thấy trang', dirname(__DIR__) . '/app/views/404.php');
