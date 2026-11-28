<?php
declare(strict_types=1);

class AppController
{
    private PDO $db;
    private string $views;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->views = dirname(__DIR__) . '/views';
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }

    private function requireAdmin(): void
    {
        $this->requireLogin();
        if ($_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            renderLayout('Không có quyền', $this->views . '/403.php');
            exit;
        }
    }

    private function requireSeller(): void
    {
        $this->requireLogin();
        if ($_SESSION['user']['role'] !== 'seller') {
            http_response_code(403);
            renderLayout('Không có quyền', $this->views . '/403.php');
            exit;
        }
    }

    private function verifyCsrf(): bool
    {
        return isset($_POST['csrf'], $_SESSION['csrf']) &&
            hash_equals($_SESSION['csrf'], $_POST['csrf']);
    }
    private function makeSlug(string $value): string
    {
        $value = trim($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
        return trim($value, '-') ?: 'bai-viet';
    }

    public function home(): void
    {
        $featured = $this->db->query("SELECT * FROM products WHERE featured=1 ORDER BY id DESC LIMIT 6")->fetchAll();
        renderLayout('Trang chủ', $this->views . '/home.php', ['featured' => $featured]);
    }

    public function about(): void { renderLayout('Giới thiệu', $this->views . '/about.php'); }
    public function pricing(): void
    {
        $products = $this->db->query("SELECT * FROM products ORDER BY price ASC")->fetchAll();
        renderLayout('Bảng giá', $this->views . '/pricing.php', ['products' => $products]);
    }
    public function faq(): void
    {
        $faqs = $this->db->query("SELECT * FROM faqs WHERE is_active=1")->fetchAll();
        renderLayout('Hỏi đáp', $this->views . '/faq.php', ['faqs' => $faqs]);
    }
    public function robots(): void
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\n\n";
        echo "Disallow: /admin/\nDisallow: /seller/\nDisallow: /cart\nDisallow: /checkout\nDisallow: /profile\nDisallow: /orders\n";
        echo "\nSitemap: {$scheme}://{$host}/sitemap.xml\n";
    }
    public function sitemap(): void
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;
        $urls = [
            ['loc' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => '/about', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => '/products', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => '/pricing', 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => '/faq', 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => '/news', 'changefreq' => 'daily', 'priority' => '0.8'],
        ];
        foreach ($this->db->query("SELECT slug FROM products WHERE is_available=1 AND stock>0") as $row) {
            $urls[] = ['loc' => '/products/' . rawurlencode($row['slug']), 'changefreq' => 'weekly', 'priority' => '0.8'];
        }
        foreach ($this->db->query("SELECT slug FROM posts WHERE is_published=1") as $row) {
            $urls[] = ['loc' => '/news/' . rawurlencode($row['slug']), 'changefreq' => 'weekly', 'priority' => '0.7'];
        }
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            echo '<url><loc>' . htmlspecialchars($baseUrl . $url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            echo '<changefreq>' . $url['changefreq'] . '</changefreq>';
            echo '<priority>' . $url['priority'] . '</priority></url>';
        }
        echo '</urlset>';
    }
    public function news(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;
        $params = [];
        $where = 'is_published=1';
        if ($search !== '') {
            $where .= ' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
            $term = '%' . $search . '%';
            $params = [$term, $term, $term];
        }
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE {$where}");
        $countStmt->execute($params);
        $totalRows = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $pages);
        $sql = "SELECT * FROM posts WHERE {$where} ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        renderLayout('Tin tức', $this->views . '/news.php', [
            'posts' => $stmt->fetchAll(),
            'search' => $search,
            'page' => $page,
            'pages' => $pages,
        ]);
    }
    public function newsDetail(string $slug): void
    {
        $stmt = $this->db->prepare('SELECT p.*,u.name AS author_name FROM posts p LEFT JOIN users u ON u.id=p.author_id WHERE p.slug=? AND p.is_published=1');
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
        if (!$post) {
            http_response_code(404);
            renderLayout('Không tìm thấy bài viết', $this->views . '/404.php');
            return;
        }
        $commentsStmt = $this->db->prepare("
            SELECT c.*,u.name AS author_name
            FROM post_comments c
            LEFT JOIN users u ON u.id=c.user_id
            WHERE c.post_id=? AND c.is_approved=1
            ORDER BY c.created_at DESC,c.id DESC
        ");
        $commentsStmt->execute([$post['id']]);
        renderLayout($post['title'], $this->views . '/news-detail.php', [
            'post' => $post,
            'comments' => $commentsStmt->fetchAll(),
            'commentError' => null,
        ]);
    }
    public function newsCommentPost(): void
    {
        $this->requireLogin();
        if (!$this->verifyCsrf()) {
            http_response_code(400);
            echo 'Phiên làm việc không hợp lệ';
            return;
        }
        $postId = filter_var($_POST['post_id'] ?? null, FILTER_VALIDATE_INT);
        $content = trim((string) ($_POST['content'] ?? ''));
        $postStmt = $this->db->prepare('SELECT slug FROM posts WHERE id=? AND is_published=1');
        $postStmt->execute([$postId]);
        $post = $postStmt->fetch();
        if (!$post) {
            http_response_code(404);
            echo 'Không tìm thấy bài viết';
            return;
        }
        if (mb_strlen($content) < 3) {
            header('Location: /news/' . rawurlencode($post['slug']) . '?comment_error=1');
            exit;
        }
        $insert = $this->db->prepare('INSERT INTO post_comments(post_id,user_id,content,is_approved) VALUES(?,?,?,1)');
        $insert->execute([$postId, $_SESSION['user']['id'], $content]);
        header('Location: /news/' . rawurlencode($post['slug']) . '#comments');
        exit;
    }
    public function login(): void
    {
        if (isset($_SESSION['user'])) {
            $destination = $_SESSION['user']['role'] === 'admin'
                ? '/admin'
                : ($_SESSION['user']['role'] === 'seller' ? '/seller' : '/');
            header('Location: ' . $destination);
            exit;
        }
        renderLayout('Đăng nhập', $this->views . '/login.php', ['error' => null]);
    }
    public function register(): void
    {
        renderLayout('Đăng ký', $this->views . '/register.php', ['error' => null]);
    }
    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    }
    public function profile(): void
    {
        $this->requireLogin();
        $statement = $this->db->prepare('SELECT name,email,phone,address FROM users WHERE id=?');
        $statement->execute([$_SESSION['user']['id']]);
        $user = $statement->fetch();
        if (!$user) {
            http_response_code(404);
            renderLayout('Không tìm thấy tài khoản', $this->views . '/404.php');
            return;
        }
        $_SESSION['user']['name'] = $user['name'];
        $_SESSION['user']['email'] = $user['email'];
        $_SESSION['user']['phone'] = $user['phone'];
        $_SESSION['user']['address'] = $user['address'];
        renderLayout('Tài khoản', $this->views . '/profile.php', [
            'user' => $user,
            'error' => null,
            'success' => null,
        ]);
    }
    public function orderHistory(): void
    {
        $this->requireLogin();
        $statement = $this->db->prepare('SELECT id,total,status,created_at FROM orders WHERE user_id=? ORDER BY created_at DESC,id DESC');
        $statement->execute([$_SESSION['user']['id']]);
        $orders = $statement->fetchAll();

        if ($orders) {
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemStatement = $this->db->prepare("SELECT order_id,name,quantity FROM order_items WHERE order_id IN ($placeholders) ORDER BY id");
            $itemStatement->execute($orderIds);
            $itemsByOrder = [];
            foreach ($itemStatement->fetchAll() as $item) {
                $itemsByOrder[$item['order_id']][] = $item;
            }
            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrder[$order['id']] ?? [];
            }
            unset($order);
        }

        renderLayout('Lịch sử mua hàng', $this->views . '/order-history.php', ['orders' => $orders]);
    }
    public function cart(): void
    {
        $items = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($items as $item) { $total += $item['price'] * $item['quantity']; }
        renderLayout('Giỏ hàng', $this->views . '/cart.php', ['items' => $items, 'total' => $total]);
    }
    public function checkout(): void
    {
        $this->requireLogin();
        $items = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($items as $item) { $total += $item['price'] * $item['quantity']; }
        if (!$items) { header('Location: /cart'); exit; }
        $profileStatement = $this->db->prepare('SELECT name,phone,address FROM users WHERE id=?');
        $profileStatement->execute([$_SESSION['user']['id']]);
        $profile = $profileStatement->fetch();
        if (!$profile) {
            http_response_code(404);
            renderLayout('Không tìm thấy tài khoản', $this->views . '/404.php');
            return;
        }
        $checkoutData = [
            'name' => $profile['name'] ?? '',
            'phone' => $profile['phone'] ?? '',
            'address' => $profile['address'] ?? '',
            'note' => '',
        ];
        $saveDefaultChecked = empty($checkoutData['name']) ||
            empty($checkoutData['phone']) || empty($checkoutData['address']);
        renderLayout('Thanh toán', $this->views . '/checkout.php', [
            'items' => $items,
            'total' => $total,
            'checkoutData' => $checkoutData,
            'saveDefaultChecked' => $saveDefaultChecked,
            'error' => null,
        ]);
    }

    public function products(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $category = (int) ($_GET['category'] ?? 0);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE ? OR short_description LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($category > 0) {
            $where[] = 'category_id=?';
            $params[] = $category;
        }
        $sql = 'SELECT * FROM products';
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $countSql = 'SELECT COUNT(*) FROM products';
        if ($where) { $countSql .= ' WHERE ' . implode(' AND ', $where); }
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalRows = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $pages);
        $sql .= ' ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        $categories = $this->db->query('SELECT * FROM categories')->fetchAll();
        $returnTo = '/products?' . http_build_query([
            'q' => $search,
            'category' => $category,
            'page' => $page,
        ]);
        renderLayout('Sản phẩm', $this->views . '/products.php', [
            'products' => $products, 'categories' => $categories,
            'search' => $search, 'category' => $category, 'page' => $page, 'pages' => $pages,
            'returnTo' => $returnTo,
        ]);
    }

    public function productDetail(string $slug): void
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE slug=?');
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
        if (!$product) {
            http_response_code(404);
            renderLayout('Không tìm thấy món', $this->views . '/404.php');
            return;
        }
        renderLayout($product['name'], $this->views . '/product.php', ['product' => $product]);
    }

    public function registerPost(): void
    {
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6) {
            renderLayout('Đăng ký', $this->views . '/register.php', ['error' => 'Họ tên tối thiểu 2 ký tự, email hợp lệ và mật khẩu tối thiểu 6 ký tự.']);
            return;
        }
        if ($password !== $passwordConfirmation) {
            renderLayout('Đăng ký', $this->views . '/register.php', ['error' => 'Mật khẩu nhập lại không khớp.']);
            return;
        }
        $check = $this->db->prepare('SELECT id FROM users WHERE email=?');
        $check->execute([$email]);
        if ($check->fetch()) {
            renderLayout('Đăng ký', $this->views . '/register.php', ['error' => 'Email đã được sử dụng.']);
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: /login');
        exit;
    }

    public function loginPost(): void
    {
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email=?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || $user['blocked'] || !password_verify($password, $user['password'])) {
            renderLayout('Đăng nhập', $this->views . '/login.php', ['error' => 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khoá.']);
            return;
        }
        $_SESSION['user'] = [
            'id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'],
            'role' => $user['role'], 'avatar' => $user['avatar'] ?? null
        ];
        $_SESSION['flash_alert'] = 'Đăng nhập thành công.';
        $destination = $user['role'] === 'admin'
            ? '/admin'
            : ($user['role'] === 'seller' ? '/seller' : '/');
        header('Location: ' . $destination);
        exit;
    }

    public function cartAdd(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $quantity = max(1, min(20, (int) ($_POST['quantity'] ?? 1)));
        $returnTo = (string) ($_POST['return_to'] ?? '/products');
        $returnParts = parse_url($returnTo);
        $returnPath = is_array($returnParts) ? ($returnParts['path'] ?? '') : '';
        $validProductPath = is_string($returnPath) &&
            preg_match('#^/products/[A-Za-z0-9._%~-]+$#D', $returnPath) === 1;
        $validReturnPath = $returnPath === '/' || $returnPath === '/products' || $validProductPath;
        if (!is_array($returnParts) || isset($returnParts['scheme']) || isset($returnParts['host']) ||
            strpos($returnTo, '\\') !== false || preg_match('/[\r\n]/', $returnTo) ||
            !$validReturnPath) {
            http_response_code(400);
            echo 'Trang quay lại không hợp lệ';
            return;
        }

        $stmt = $this->db->prepare('SELECT * FROM products WHERE id=?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product && (int) $product['is_available'] === 1 && (int) $product['stock'] > 0) {
            $_SESSION['cart'] ??= [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = min(20, $_SESSION['cart'][$id]['quantity'] + $quantity);
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $product['id'], 'name' => $product['name'],
                    'price' => $product['price'], 'quantity' => $quantity
                ];
            }
            $_SESSION['flash_alert'] = 'Đã thêm món vào giỏ hàng.';
        } elseif ($product) {
            $_SESSION['flash_alert'] = 'Món này hiện đang tạm hết.';
        } else {
            http_response_code(404);
            echo 'Không tìm thấy món ăn';
            return;
        }
        header('Location: ' . $returnTo);
        exit;
    }

    public function cartUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        if ($quantity <= 0) unset($_SESSION['cart'][$id]);
        elseif (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]['quantity'] = min(20, $quantity);
        header('Location: /cart');
        exit;
    }

    public function checkoutPost(): void
    {
        $this->requireLogin();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $note = trim((string) ($_POST['note'] ?? ''));
        $checkoutData = ['name' => $name, 'phone' => $phone, 'address' => $address, 'note' => $note];
        $items = $_SESSION['cart'] ?? [];
        $profileStatement = $this->db->prepare('SELECT name,phone,address FROM users WHERE id=?');
        $profileStatement->execute([$_SESSION['user']['id']]);
        $profile = $profileStatement->fetch();
        if (!$profile) {
            http_response_code(404);
            renderLayout('Không tìm thấy tài khoản', $this->views . '/404.php');
            return;
        }
        $profileIsIncomplete = empty($profile['name']) || empty($profile['phone']) || empty($profile['address']);
        $saveDefaultChecked = isset($_POST['save_default']) || $profileIsIncomplete;
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100 ||
            !preg_match('/^[0-9]{8,15}$/D', $phone) ||
            mb_strlen($address) < 10 || mb_strlen($address) > 255 || !$items) {
            $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $items));
            renderLayout('Thanh toán', $this->views . '/checkout.php', [
                'items' => $items,
                'total' => $total,
                'checkoutData' => $checkoutData,
                'saveDefaultChecked' => $saveDefaultChecked,
                'error' => 'Vui lòng nhập tên, số điện thoại gồm 8–15 chữ số và địa chỉ giao hàng hợp lệ.',
            ]);
            return;
        }
        $orderItems = [];
        foreach ($items as $item) {
            $productStatement = $this->db->prepare('SELECT id,name,price,seller_id FROM products WHERE id=?');
            $productStatement->execute([(int) $item['id']]);
            $product = $productStatement->fetch();
            if (!$product) {
                renderLayout('Thanh toán', $this->views . '/checkout.php', [
                    'items' => $items,
                    'total' => 0,
                    'checkoutData' => $checkoutData,
                    'saveDefaultChecked' => $saveDefaultChecked,
                    'error' => 'Một sản phẩm trong giỏ không còn tồn tại. Vui lòng kiểm tra lại giỏ hàng.',
                ]);
                return;
            }
            $orderItems[] = [
                'id' => (int) $product['id'],
                'name' => $product['name'],
                'price' => (int) $product['price'],
                'quantity' => max(1, min(20, (int) $item['quantity'])),
                'seller_id' => $product['seller_id'] === null ? null : (int) $product['seller_id']
            ];
        }
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $orderItems));

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO orders(user_id,customer_name,phone,address,note,total) VALUES(?,?,?,?,?,?)');
            $stmt->execute([$_SESSION['user']['id'], $name, $phone, $address, $note, $total]);
            $orderId = (int) $this->db->lastInsertId();
            $itemStmt = $this->db->prepare('INSERT INTO order_items(order_id,product_id,name,price,quantity,seller_id) VALUES(?,?,?,?,?,?)');
            foreach ($orderItems as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['quantity'], $item['seller_id']]);
            }
            if ($saveDefaultChecked) {
                $profileUpdate = $this->db->prepare('UPDATE users SET name=?,phone=?,address=? WHERE id=?');
                $profileUpdate->execute([$name, $phone, $address, $_SESSION['user']['id']]);
            }
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
        if ($saveDefaultChecked) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
        }
        unset($_SESSION['cart']);
        renderLayout('Đặt hàng thành công', $this->views . '/order-success.php', ['orderId' => $orderId]);
    }

    public function profilePost(): void
    {
        $this->requireLogin();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $profileUser = $_SESSION['user'];
        $profileUser['name'] = $name;
        $profileUser['phone'] = $phone;
        $profileUser['address'] = $address;
        if (mb_strlen($name) < 2) {
            renderLayout('Tài khoản', $this->views . '/profile.php', [
                'user' => $profileUser,
                'error' => 'Họ tên không hợp lệ.',
                'success' => null,
            ]);
            return;
        }
        if ($phone !== '' && !preg_match('/^[0-9]{8,15}$/D', $phone)) {
            renderLayout('Tài khoản', $this->views . '/profile.php', [
                'user' => $profileUser,
                'error' => 'Số điện thoại phải có từ 8 đến 15 chữ số.',
                'success' => null,
            ]);
            return;
        }
        if (mb_strlen($address) > 255) {
            renderLayout('Tài khoản', $this->views . '/profile.php', [
                'user' => $profileUser,
                'error' => 'Địa chỉ không được vượt quá 255 ký tự.',
                'success' => null,
            ]);
            return;
        }
        $stmt = $this->db->prepare('UPDATE users SET name=?,phone=?,address=? WHERE id=?');
        $stmt->execute([$name, $phone, $address, $_SESSION['user']['id']]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['address'] = $address;
        $profileUser['email'] = $_SESSION['user']['email'];
        renderLayout('Tài khoản', $this->views . '/profile.php', [
            'user' => $profileUser,
            'error' => null,
            'success' => 'Cập nhật thông tin thành công.',
        ]);
    }

    public function changePassword(): void
    {
        $this->requireLogin();
        renderLayout('Đổi mật khẩu', $this->views . '/change-password.php', [
            'error' => null,
            'success' => null,
        ]);
    }

    public function changePasswordPost(): void
    {
        $this->requireLogin();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }

        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $error = null;
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Vui lòng nhập đầy đủ các trường mật khẩu.';
        } elseif (mb_strlen($newPassword) < 6 || mb_strlen($newPassword) > 255) {
            $error = 'Mật khẩu mới phải có từ 6 đến 255 ký tự.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Mật khẩu mới nhập lại không khớp.';
        } else {
            $statement = $this->db->prepare('SELECT password FROM users WHERE id=?');
            $statement->execute([$_SESSION['user']['id']]);
            $passwordHash = $statement->fetchColumn();
            if (!is_string($passwordHash)) {
                http_response_code(404);
                renderLayout('Không tìm thấy tài khoản', $this->views . '/404.php');
                return;
            }
            if (!password_verify($currentPassword, $passwordHash)) {
                $error = 'Mật khẩu hiện tại không đúng.';
            } else {
                $update = $this->db->prepare('UPDATE users SET password=? WHERE id=?');
                $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user']['id']]);
            }
        }

        renderLayout('Đổi mật khẩu', $this->views . '/change-password.php', [
            'error' => $error,
            'success' => $error === null ? 'Đổi mật khẩu thành công.' : null,
        ]);
    }

    public function seller(): void
    {
        $this->requireSeller();
        $sellerId = (int) $_SESSION['user']['id'];
        $productPage = max(1, (int) ($_GET['product_page'] ?? 1));
        $orderPage = max(1, (int) ($_GET['order_page'] ?? 1));
        $perPage = 10;

        $productCount = $this->db->prepare('SELECT COUNT(*) FROM products WHERE seller_id=?');
        $productCount->execute([$sellerId]);
        $productTotal = (int) $productCount->fetchColumn();
        $productPages = max(1, (int) ceil($productTotal / $perPage));
        $productPage = min($productPage, $productPages);
        $products = $this->db->prepare('
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.seller_id = ?
            ORDER BY p.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . (($productPage - 1) * $perPage) . '
        ');
        $products->execute([$sellerId]);

        $orderCount = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE seller_id=?');
        $orderCount->execute([$sellerId]);
        $orderPages = max(1, (int) ceil((int) $orderCount->fetchColumn() / $perPage));
        $orderPage = min($orderPage, $orderPages);
        $orderItems = $this->db->prepare('
            SELECT oi.id AS order_item_id, oi.order_id, oi.name AS product_name,
                   oi.price, oi.quantity, oi.status AS item_status,
                   o.customer_name, o.phone, o.address, o.note, o.created_at
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE oi.seller_id = ?
            ORDER BY o.id DESC, oi.id DESC
            LIMIT ' . $perPage . ' OFFSET ' . (($orderPage - 1) * $perPage) . '
        ');
        $orderItems->execute([$sellerId]);
        $products = $products->fetchAll();
        $orderItems = $orderItems->fetchAll();
        $statsStatement = $this->db->prepare("
            SELECT COUNT(DISTINCT order_id) AS orders,
                   COUNT(DISTINCT CASE WHEN status='pending' THEN order_id END) AS pending_orders,
                   COALESCE(SUM(CASE WHEN status='completed' THEN price * quantity ELSE 0 END), 0) AS revenue
            FROM order_items
            WHERE seller_id=?
        ");
        $statsStatement->execute([$sellerId]);
        $stats = $statsStatement->fetch();
        $notices = [
            'product-saved' => 'Đã lưu sản phẩm.',
            'product-deleted' => 'Đã xoá sản phẩm.',
            'availability-updated' => 'Đã cập nhật trạng thái bán món.',
            'status-saved' => 'Đã cập nhật trạng thái món trong đơn.',
            'product-in-order' => 'Không thể xoá sản phẩm đã có trong đơn hàng.',
        ];
        renderLayout('Khu vực người bán', $this->views . '/seller.php', [
            'products' => $products,
            'orderItems' => $orderItems,
            'stats' => $stats,
            'notice' => $notices[(string) ($_GET['notice'] ?? '')] ?? null,
            'isError' => ($_GET['notice'] ?? '') === 'product-in-order',
            'productPage' => $productPage,
            'productPages' => $productPages,
            'orderPage' => $orderPage,
            'orderPages' => $orderPages,
            'productTotal' => $productTotal,
        ]);
    }

    public function sellerRevenue(): void
    {
        $this->requireSeller();
        $sellerId = (int) $_SESSION['user']['id'];
        $summary = $this->db->prepare("
            SELECT COUNT(DISTINCT order_id) AS completed_orders,
                   COALESCE(SUM(price * quantity), 0) AS total_revenue
            FROM order_items
            WHERE seller_id=? AND status='completed'
        ");
        $summary->execute([$sellerId]);
        $daily = $this->db->prepare("
            SELECT DATE(o.created_at) AS period, COUNT(DISTINCT o.id) AS orders,
                   SUM(oi.price * oi.quantity) AS revenue
            FROM order_items oi
            INNER JOIN orders o ON o.id=oi.order_id
            WHERE oi.seller_id=? AND oi.status='completed'
            GROUP BY DATE(o.created_at)
            ORDER BY period DESC
            LIMIT 30
        ");
        $daily->execute([$sellerId]);
        $monthly = $this->db->prepare("
            SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS period, COUNT(DISTINCT o.id) AS orders,
                   SUM(oi.price * oi.quantity) AS revenue
            FROM order_items oi
            INNER JOIN orders o ON o.id=oi.order_id
            WHERE oi.seller_id=? AND oi.status='completed'
            GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
            ORDER BY period DESC
            LIMIT 12
        ");
        $monthly->execute([$sellerId]);
        renderLayout('Doanh thu người bán', $this->views . '/seller-revenue.php', [
            'summary' => $summary->fetch(),
            'dailyRevenue' => $daily->fetchAll(),
            'monthlyRevenue' => $monthly->fetchAll(),
        ]);
    }

    public function sellerProductNew(): void
    {
        $this->requireSeller();
        $this->renderSellerProductForm([
            'id' => 0,
            'category_id' => null,
            'name' => '',
            'slug' => '',
            'short_description' => '',
            'description' => '',
            'price' => '',
            'stock' => 0,
            'image' => null,
        ], null);
    }

    public function sellerProductEdit(int $id): void
    {
        $this->requireSeller();
        $statement = $this->db->prepare('SELECT * FROM products WHERE id=? AND seller_id=?');
        $statement->execute([$id, $_SESSION['user']['id']]);
        $product = $statement->fetch();
        if (!$product) {
            http_response_code(404);
            renderLayout('Không tìm thấy sản phẩm', $this->views . '/404.php');
            return;
        }
        $this->renderSellerProductForm($product, null);
    }

    private function renderSellerProductForm(array $product, ?string $error): void
    {
        $categories = $this->db->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
        renderLayout('Sản phẩm của người bán', $this->views . '/seller-product-form.php', [
            'product' => $product,
            'categories' => $categories,
            'error' => $error,
        ]);
    }

    public function sellerProductSave(): void
    {
        $this->requireSeller();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }

        $id = max(0, (int) ($_POST['id'] ?? 0));
        $existingProduct = null;
        if ($id > 0) {
            $existing = $this->db->prepare('SELECT id,image FROM products WHERE id=? AND seller_id=?');
            $existing->execute([$id, $_SESSION['user']['id']]);
            $existingProduct = $existing->fetch();
            if (!$existingProduct) {
                http_response_code(404);
                renderLayout('Không tìm thấy sản phẩm', $this->views . '/404.php');
                return;
            }
        }
        $product = [
            'id' => $id,
            'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => strtolower(trim((string) ($_POST['slug'] ?? ''))),
            'short_description' => trim((string) ($_POST['short_description'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')),
            'stock' => trim((string) ($_POST['stock'] ?? '')),
            'image' => $existingProduct['image'] ?? null,
        ];
        $price = filter_var($product['price'], FILTER_VALIDATE_INT);
        $stock = filter_var($product['stock'], FILTER_VALIDATE_INT);
        $valid = mb_strlen($product['name']) >= 2 &&
            mb_strlen($product['name']) <= 150 &&
            preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $product['slug']) &&
            mb_strlen($product['slug']) <= 180 &&
            mb_strlen($product['short_description']) <= 255 &&
            $price !== false && $price > 0 && $price <= 4294967295 &&
            $stock !== false && $stock >= 0 && $stock <= 4294967295;
        if (!$valid) {
            $this->renderSellerProductForm($product, 'Vui lòng nhập tên, slug hợp lệ, giá và tồn kho hợp lệ.');
            return;
        }
        if ($product['category_id'] !== null) {
            $category = $this->db->prepare('SELECT id FROM categories WHERE id=?');
            $category->execute([$product['category_id']]);
            if (!$category->fetch()) {
                $this->renderSellerProductForm($product, 'Danh mục đã chọn không tồn tại.');
                return;
            }
        }
        $duplicate = $this->db->prepare('SELECT id FROM products WHERE slug=? AND id<>?');
        $duplicate->execute([$product['slug'], $id]);
        if ($duplicate->fetch()) {
            $this->renderSellerProductForm($product, 'Slug này đã được sản phẩm khác sử dụng.');
            return;
        }

        $newImageFile = null;
        $imageUpload = $_FILES['image'] ?? null;
        if (is_array($imageUpload) && ($imageUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($imageUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = in_array($imageUpload['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? 'Ảnh vượt quá giới hạn tải lên của máy chủ.'
                    : 'Không thể tải ảnh lên. Vui lòng thử lại.';
                $this->renderSellerProductForm($product, $message);
                return;
            }
            if ((int) ($imageUpload['size'] ?? 0) > 5 * 1024 * 1024) {
                $this->renderSellerProductForm($product, 'Ảnh không được lớn hơn 5 MB.');
                return;
            }

            $temporaryPath = (string) ($imageUpload['tmp_name'] ?? '');
            if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
                $this->renderSellerProductForm($product, 'Tệp ảnh tải lên không hợp lệ.');
                return;
            }
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($fileInfo === false) {
                $this->renderSellerProductForm($product, 'Không thể kiểm tra định dạng ảnh trên máy chủ.');
                return;
            }
            $mimeType = finfo_file($fileInfo, $temporaryPath);
            finfo_close($fileInfo);
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $imageInfo = getimagesize($temporaryPath);
            if (!isset($allowedTypes[$mimeType]) || !is_array($imageInfo) ||
                ($imageInfo['mime'] ?? null) !== $mimeType ||
                $imageInfo[0] > 10000 || $imageInfo[1] > 10000 ||
                $imageInfo[0] * $imageInfo[1] > 40000000) {
                $this->renderSellerProductForm($product, 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP có kích thước hợp lệ.');
                return;
            }

            $uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/products';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                $this->renderSellerProductForm($product, 'Không thể tạo thư mục lưu ảnh trên máy chủ.');
                return;
            }
            $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
            $newImageFile = $uploadDirectory . '/' . $filename;
            if (!move_uploaded_file($temporaryPath, $newImageFile)) {
                $newImageFile = null;
                $this->renderSellerProductForm($product, 'Không thể lưu ảnh trên máy chủ.');
                return;
            }
            $product['image'] = '/uploads/products/' . $filename;
        }

        try {
            if ($id > 0) {
                $statement = $this->db->prepare('
                    UPDATE products
                    SET category_id=?, name=?, slug=?, short_description=?, description=?, price=?, stock=?, image=?
                    WHERE id=? AND seller_id=?
                ');
                $statement->execute([
                    $product['category_id'], $product['name'], $product['slug'],
                    $product['short_description'], $product['description'], $price,
                    $stock, $product['image'], $id, $_SESSION['user']['id']
                ]);
            } else {
                $statement = $this->db->prepare('
                    INSERT INTO products(category_id,name,slug,short_description,description,price,stock,featured,seller_id,image)
                    VALUES(?,?,?,?,?,?,?,0,?,?)
                ');
                $statement->execute([
                    $product['category_id'], $product['name'], $product['slug'],
                    $product['short_description'], $product['description'], $price,
                    $stock, $_SESSION['user']['id'], $product['image']
                ]);
            }
        } catch (PDOException $error) {
            if ($newImageFile !== null && is_file($newImageFile) && !unlink($newImageFile)) {
                error_log('Unable to remove uploaded product image after database failure: ' . $newImageFile);
            }
            throw $error;
        }
        header('Location: /seller?notice=product-saved');
        exit;
    }

    public function sellerProductDelete(): void
    {
        $this->requireSeller();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $id = (int) ($_POST['id'] ?? 0);
        $product = $this->db->prepare('SELECT id FROM products WHERE id=? AND seller_id=?');
        $product->execute([$id, $_SESSION['user']['id']]);
        if (!$product->fetch()) {
            http_response_code(404);
            renderLayout('Không tìm thấy sản phẩm', $this->views . '/404.php');
            return;
        }
        $orderItems = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE product_id=?');
        $orderItems->execute([$id]);
        if ((int) $orderItems->fetchColumn() > 0) {
            header('Location: /seller?notice=product-in-order');
            exit;
        }
        $delete = $this->db->prepare('DELETE FROM products WHERE id=? AND seller_id=?');
        $delete->execute([$id, $_SESSION['user']['id']]);
        header('Location: /seller?notice=product-deleted');
        exit;
    }

    public function sellerProductAvailability(): void
    {
        $this->requireSeller();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $id = (int) ($_POST['id'] ?? 0);
        $statement = $this->db->prepare('UPDATE products SET is_available=IF(is_available=1,0,1) WHERE id=? AND seller_id=?');
        $statement->execute([$id, $_SESSION['user']['id']]);
        if ($statement->rowCount() === 0) {
            $check = $this->db->prepare('SELECT id FROM products WHERE id=? AND seller_id=?');
            $check->execute([$id, $_SESSION['user']['id']]);
            if (!$check->fetch()) {
                http_response_code(404);
                renderLayout('Không tìm thấy sản phẩm', $this->views . '/404.php');
                return;
            }
        }
        header('Location: /seller?notice=availability-updated#products');
        exit;
    }

    public function sellerOrderItemStatus(): void
    {
        $this->requireSeller();
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $id = (int) ($_POST['order_item_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');
        $allowedStatuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            http_response_code(400);
            echo 'Trạng thái không hợp lệ';
            return;
        }
        $sellerId = (int) $_SESSION['user']['id'];
        $orderLookup = $this->db->prepare('SELECT order_id FROM order_items WHERE id=? AND seller_id=?');
        $orderLookup->execute([$id, $sellerId]);
        $orderId = $orderLookup->fetchColumn();
        if ($orderId === false) {
            http_response_code(404);
            renderLayout('Không tìm thấy món trong đơn hàng', $this->views . '/404.php');
            return;
        }

        $this->db->beginTransaction();
        try {
            $orderLock = $this->db->prepare('SELECT id FROM orders WHERE id=? FOR UPDATE');
            $orderLock->execute([$orderId]);
            if ($orderLock->fetchColumn() === false) {
                $this->db->rollBack();
                http_response_code(404);
                renderLayout('Không tìm thấy đơn hàng', $this->views . '/404.php');
                return;
            }

            $itemLock = $this->db->prepare('SELECT id FROM order_items WHERE id=? AND order_id=? AND seller_id=? FOR UPDATE');
            $itemLock->execute([$id, $orderId, $sellerId]);
            if ($itemLock->fetchColumn() === false) {
                $this->db->rollBack();
                http_response_code(404);
                renderLayout('Không tìm thấy món trong đơn hàng', $this->views . '/404.php');
                return;
            }

            $updateItem = $this->db->prepare('UPDATE order_items SET status=? WHERE id=? AND seller_id=?');
            $updateItem->execute([$status, $id, $sellerId]);

            $itemStatuses = $this->db->prepare('SELECT status FROM order_items WHERE order_id=? FOR UPDATE');
            $itemStatuses->execute([$orderId]);
            $allStatuses = $itemStatuses->fetchAll(PDO::FETCH_COLUMN);
            $allCancelled = count($allStatuses) > 0;
            $hasCompleted = false;
            $hasPending = false;
            $hasConfirmed = false;
            $hasShipping = false;
            foreach ($allStatuses as $itemStatus) {
                if ($itemStatus !== 'cancelled') {
                    $allCancelled = false;
                }
                if ($itemStatus === 'completed') {
                    $hasCompleted = true;
                } elseif ($itemStatus === 'pending') {
                    $hasPending = true;
                } elseif ($itemStatus === 'confirmed') {
                    $hasConfirmed = true;
                } elseif ($itemStatus === 'shipping') {
                    $hasShipping = true;
                }
            }

            if ($allCancelled) {
                $overallStatus = 'cancelled';
            } elseif ($hasPending) {
                $overallStatus = 'pending';
            } elseif ($hasConfirmed) {
                $overallStatus = 'confirmed';
            } elseif ($hasShipping) {
                $overallStatus = 'shipping';
            } elseif ($hasCompleted) {
                $overallStatus = 'completed';
            } else {
                $overallStatus = 'pending';
            }

            $updateOrder = $this->db->prepare('UPDATE orders SET status=? WHERE id=?');
            $updateOrder->execute([$overallStatus, $orderId]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
        header('Location: /seller?notice=status-saved');
        exit;
    }

    public function adminPostNew(): void
    {
        $this->requireAdmin();
        renderLayout('Thêm bài viết', $this->views . '/admin-post-form.php', [
            'post' => null,
            'error' => null,
        ]);
    }

    public function adminPostEdit(int $id): void
    {
        $this->requireAdmin();
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id=?');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) {
            http_response_code(404);
            renderLayout('Không tìm thấy bài viết', $this->views . '/404.php');
            return;
        }
        renderLayout('Sửa bài viết', $this->views . '/admin-post-form.php', [
            'post' => $post,
            'error' => null,
        ]);
    }

    public function adminPostSave(): void
    {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) {
            http_response_code(400);
            echo 'Phiên làm việc không hợp lệ';
            return;
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $title = trim((string) ($_POST['title'] ?? ''));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        if (mb_strlen($title) < 3 || mb_strlen($content) < 10) {
            renderLayout('Thêm/sửa bài viết', $this->views . '/admin-post-form.php', [
                'post' => [
                    'id' => $id ?: 0,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'image' => $image,
                    'is_published' => $isPublished,
                ],
                'error' => 'Tiêu đề tối thiểu 3 ký tự và nội dung tối thiểu 10 ký tự.',
            ]);
            return;
        }
        $baseSlug = $this->makeSlug($title);
        $slug = $baseSlug;
        $suffix = 2;
        while (true) {
            $check = $this->db->prepare('SELECT id FROM posts WHERE slug=? AND id<>?');
            $check->execute([$slug, $id ?: 0]);
            if (!$check->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $suffix++;
        }
        if ($id && $id > 0) {
            $stmt = $this->db->prepare('UPDATE posts SET title=?,slug=?,excerpt=?,content=?,image=?,is_published=?,published_at=IF(?=1,COALESCE(published_at,CURRENT_TIMESTAMP),NULL) WHERE id=?');
            $stmt->execute([$title, $slug, $excerpt, $content, $image ?: null, $isPublished, $isPublished, $id]);
        } else {
            $stmt = $this->db->prepare('INSERT INTO posts(title,slug,excerpt,content,image,author_id,is_published,published_at) VALUES(?,?,?,?,?,?,?,IF(?=1,CURRENT_TIMESTAMP,NULL))');
            $stmt->execute([$title, $slug, $excerpt, $content, $image ?: null, $_SESSION['user']['id'], $isPublished, $isPublished]);
        }
        header('Location: /admin/posts');
        exit;
    }

    public function adminPostDelete(): void
    {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) {
            http_response_code(400);
            echo 'Phiên làm việc không hợp lệ';
            return;
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            http_response_code(400);
            echo 'Bài viết không hợp lệ';
            return;
        }
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id=?');
        $stmt->execute([$id]);
        header('Location: /admin/posts');
        exit;
    }

    public function admin(): void
    {
        $this->requireAdmin();
        $stats = [
            'users' => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'products' => (int) $this->db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'orders' => (int) $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'posts' => (int) $this->db->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
            'comments' => (int) $this->db->query('SELECT COUNT(*) FROM post_comments')->fetchColumn(),
        ];
        $orders = $this->db->query("
            SELECT o.*,
                   COALESCE((
                       SELECT GROUP_CONCAT(DISTINCT COALESCE(s.name, 'Quản trị viên') ORDER BY s.name SEPARATOR ', ')
                       FROM order_items oi
                       LEFT JOIN users s ON s.id = oi.seller_id
                       WHERE oi.order_id = o.id
                   ), 'Chưa rõ') AS seller_names
            FROM orders o
            ORDER BY o.id DESC
            LIMIT 10
        ")->fetchAll();
        renderLayout('Quản trị', $this->views . '/admin.php', ['stats' => $stats, 'orders' => $orders]);
    }

    public function adminSection(string $section): void
    {
        $this->requireAdmin();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $queries = [
            'users' => "SELECT id,name,email,role,blocked,created_at FROM users ORDER BY id DESC",
            'products' => 'SELECT p.id,p.name,p.price,p.stock,p.featured,c.name AS category_name,u.name AS seller_name FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN users u ON u.id=p.seller_id ORDER BY p.id DESC',
            'orders' => "
                SELECT o.id,o.customer_name,o.phone,o.address,o.total,o.status,o.created_at,
                       COALESCE((
                           SELECT GROUP_CONCAT(DISTINCT COALESCE(s.name, 'Quản trị viên') ORDER BY s.name SEPARATOR ', ')
                           FROM order_items oi
                           LEFT JOIN users s ON s.id = oi.seller_id
                           WHERE oi.order_id = o.id
                       ), 'Chưa rõ') AS seller_names
                FROM orders o
                ORDER BY o.id DESC
            ",
            'faqs' => 'SELECT id,question,answer,is_active FROM faqs ORDER BY id DESC',
            'posts' => 'SELECT p.id,p.title,p.slug,p.is_published,p.published_at,p.created_at,u.name AS author_name FROM posts p LEFT JOIN users u ON u.id=p.author_id ORDER BY p.id DESC',
            'comments' => 'SELECT c.id,c.post_id,c.content,c.is_approved,c.created_at,p.title AS post_title,u.name AS author_name FROM post_comments c JOIN posts p ON p.id=c.post_id LEFT JOIN users u ON u.id=c.user_id ORDER BY c.created_at DESC,c.id DESC',
        ];
        if (!isset($queries[$section])) {
            http_response_code(404);
            renderLayout('Không tìm thấy trang', $this->views . '/404.php');
            return;
        }

        $countQueries = [
            'users' => 'SELECT COUNT(*) FROM users',
            'products' => 'SELECT COUNT(*) FROM products',
            'orders' => 'SELECT COUNT(*) FROM orders',
            'faqs' => 'SELECT COUNT(*) FROM faqs',
            'posts' => 'SELECT COUNT(*) FROM posts',
            'comments' => 'SELECT COUNT(*) FROM post_comments',
        ];
        $totalRows = (int) $this->db->query($countQueries[$section])->fetchColumn();
        $pages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $pages);
        $sql = $queries[$section] . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
        $records = $this->db->query($sql)->fetchAll();
        $titles = [
            'users' => 'Quản lý thành viên',
            'products' => 'Quản lý sản phẩm',
            'orders' => 'Quản lý đơn hàng',
            'faqs' => 'Quản lý FAQ',
            'posts' => 'Quản lý bài viết',
            'comments' => 'Quản lý bình luận',
        ];
        renderLayout($titles[$section], $this->views . '/admin-section.php', [
            'section' => $section,
            'sectionTitle' => $titles[$section],
            'records' => $records,
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function adminAction(): void
    {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) {
            http_response_code(400);
            echo 'Phiên làm việc không hợp lệ';
            return;
        }

        $section = (string) ($_POST['section'] ?? '');
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $action = (string) ($_POST['action'] ?? '');
        if ($id === false || $id < 1) {
            http_response_code(400);
            echo 'Mục quản lý không hợp lệ';
            return;
        }

        $updates = [
            'users' => [
                'toggle' => ['UPDATE users SET blocked=IF(blocked=1,0,1) WHERE id=? AND role<>\'admin\'', []],
            ],
            'products' => [
                'toggle' => ['UPDATE products SET featured=IF(featured=1,0,1) WHERE id=?', []],
            ],
            'orders' => [
                'status' => ['UPDATE orders SET status=? WHERE id=?', ['status']],
            ],
            'faqs' => [
                'toggle' => ['UPDATE faqs SET is_active=IF(is_active=1,0,1) WHERE id=?', []],
            ],
            'posts' => [
                'toggle' => ['UPDATE posts SET is_published=IF(is_published=1,0,1),published_at=IF(is_published=1,NULL,COALESCE(published_at,CURRENT_TIMESTAMP)) WHERE id=?', []],
            ],
            'comments' => [
                'toggle' => ['UPDATE post_comments SET is_approved=IF(is_approved=1,0,1) WHERE id=?', []],
                'delete' => ['DELETE FROM post_comments WHERE id=?', []],
            ],
        ];
        if (!isset($updates[$section][$action])) {
            http_response_code(400);
            echo 'Thao tác không hợp lệ';
            return;
        }

        $status = (string) ($_POST['status'] ?? '');
        if ($action === 'status') {
            $allowedStatuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
            if (!in_array($status, $allowedStatuses, true)) {
                http_response_code(400);
                echo 'Trạng thái không hợp lệ';
                return;
            }
        }

        [$sql, $parameters] = $updates[$section][$action];
        $values = [];
        foreach ($parameters as $parameter) {
            if ($parameter === 'status') {
                $values[] = $status;
            } else {
                $values[] = $id;
            }
        }
        $values[] = $id;
        $statement = $this->db->prepare($sql);
        $statement->execute($values);
        header('Location: /admin/' . rawurlencode($section));
        exit;
    }
}
