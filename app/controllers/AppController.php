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
    public function contact(): void
    {
        renderLayout('Liên hệ', $this->views . '/contact.php', ['contactError' => null, 'contactSuccess' => null]);
    }
    public function login(): void
    {
        if (isset($_SESSION['user'])) {
            $destination = $_SESSION['user']['role'] === 'admin'
                ? '/admin'
                : ($_SESSION['user']['role'] === 'seller' ? '/seller' : '/profile');
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
        renderLayout('Tài khoản', $this->views . '/profile.php', ['error' => null, 'success' => null]);
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
        $items = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($items as $item) { $total += $item['price'] * $item['quantity']; }
        if (!$items) { header('Location: /cart'); exit; }
        renderLayout('Thanh toán', $this->views . '/checkout.php', ['items' => $items, 'total' => $total, 'error' => null]);
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
        renderLayout('Sản phẩm', $this->views . '/products.php', [
            'products' => $products, 'categories' => $categories,
            'search' => $search, 'category' => $category, 'page' => $page, 'pages' => $pages
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

    public function news(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        if ($search !== '') {
            $stmt = $this->db->prepare("SELECT * FROM posts WHERE published=1 AND (title LIKE ? OR excerpt LIKE ?) ORDER BY id DESC");
            $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
        } else {
            $stmt = $this->db->query("SELECT * FROM posts WHERE published=1 ORDER BY id DESC");
        }
        renderLayout('Tin tức', $this->views . '/news.php', ['posts' => $stmt->fetchAll(), 'search' => $search]);
    }

    public function postDetail(string $slug): void
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE slug=? AND published=1');
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
        if (!$post) { http_response_code(404); renderLayout('Không tìm thấy bài viết', $this->views . '/404.php'); return; }
        renderLayout($post['title'], $this->views . '/post.php', ['post' => $post]);
    }

    public function contactPost(): void
    {
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($message) < 10) {
            renderLayout('Liên hệ', $this->views . '/contact.php', [
                'contactError' => 'Vui lòng nhập họ tên, email hợp lệ và nội dung tối thiểu 10 ký tự.',
                'contactSuccess' => null
            ]);
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO contacts(name,email,phone,message) VALUES(?,?,?,?)');
        $stmt->execute([$name, $email, $phone, $message]);
        renderLayout('Liên hệ', $this->views . '/contact.php', [
            'contactError' => null, 'contactSuccess' => 'Gửi liên hệ thành công. Chúng tôi sẽ phản hồi sớm.'
        ]);
    }

    public function registerPost(): void
    {
        if (!$this->verifyCsrf()) { http_response_code(400); echo 'Phiên làm việc không hợp lệ'; return; }
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6) {
            renderLayout('Đăng ký', $this->views . '/register.php', ['error' => 'Họ tên tối thiểu 2 ký tự, email hợp lệ và mật khẩu tối thiểu 6 ký tự.']);
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
        $destination = $user['role'] === 'admin'
            ? '/admin'
            : ($user['role'] === 'seller' ? '/seller' : '/profile');
        header('Location: ' . $destination);
        exit;
    }

    public function cartAdd(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $quantity = max(1, min(20, (int) ($_POST['quantity'] ?? 1)));
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id=?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $_SESSION['cart'] ??= [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = min(20, $_SESSION['cart'][$id]['quantity'] + $quantity);
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $product['id'], 'name' => $product['name'],
                    'price' => $product['price'], 'quantity' => $quantity
                ];
            }
        }
        header('Location: /cart');
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
        $items = $_SESSION['cart'] ?? [];
        if (mb_strlen($name) < 2 || mb_strlen($phone) < 8 || mb_strlen($address) < 10 || !$items) {
            $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $items));
            renderLayout('Thanh toán', $this->views . '/checkout.php', ['items' => $items, 'total' => $total, 'error' => 'Vui lòng nhập đầy đủ tên, số điện thoại và địa chỉ giao hàng.']);
            return;
        }
        $orderItems = [];
        foreach ($items as $item) {
            $productStatement = $this->db->prepare('SELECT id,name,price,seller_id FROM products WHERE id=?');
            $productStatement->execute([(int) $item['id']]);
            $product = $productStatement->fetch();
            if (!$product) {
                renderLayout('Thanh toán', $this->views . '/checkout.php', [
                    'items' => $items, 'total' => 0, 'error' => 'Một sản phẩm trong giỏ không còn tồn tại. Vui lòng kiểm tra lại giỏ hàng.'
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
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
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
        $password = (string) ($_POST['password'] ?? '');
        if (mb_strlen($name) < 2) {
            renderLayout('Tài khoản', $this->views . '/profile.php', ['error' => 'Họ tên không hợp lệ.', 'success' => null]);
            return;
        }
        if ($password !== '' && mb_strlen($password) >= 6) {
            $stmt = $this->db->prepare('UPDATE users SET name=?,phone=?,password=? WHERE id=?');
            $stmt->execute([$name, $phone, password_hash($password, PASSWORD_DEFAULT), $_SESSION['user']['id']]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET name=?,phone=? WHERE id=?');
            $stmt->execute([$name, $phone, $_SESSION['user']['id']]);
            if ($password !== '') {
                renderLayout('Tài khoản', $this->views . '/profile.php', ['error' => 'Mật khẩu mới tối thiểu 6 ký tự.', 'success' => null]);
                return;
            }
        }
        $_SESSION['user']['name'] = $name;
        renderLayout('Tài khoản', $this->views . '/profile.php', ['error' => null, 'success' => 'Cập nhật thông tin thành công.']);
    }

    public function seller(): void
    {
        $this->requireSeller();
        $sellerId = (int) $_SESSION['user']['id'];
        $products = $this->db->prepare('
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.seller_id = ?
            ORDER BY p.id DESC
        ');
        $products->execute([$sellerId]);
        $orderItems = $this->db->prepare('
            SELECT oi.id AS order_item_id, oi.order_id, oi.name AS product_name,
                   oi.price, oi.quantity, oi.status AS item_status,
                   o.customer_name, o.phone, o.address, o.note, o.created_at
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE oi.seller_id = ?
            ORDER BY o.id DESC, oi.id DESC
        ');
        $orderItems->execute([$sellerId]);
        $notices = [
            'product-saved' => 'Đã lưu sản phẩm.',
            'product-deleted' => 'Đã xoá sản phẩm.',
            'status-saved' => 'Đã cập nhật trạng thái món trong đơn.',
            'product-in-order' => 'Không thể xoá sản phẩm đã có trong đơn hàng.',
        ];
        renderLayout('Khu vực người bán', $this->views . '/seller.php', [
            'products' => $products->fetchAll(),
            'orderItems' => $orderItems->fetchAll(),
            'notice' => $notices[(string) ($_GET['notice'] ?? '')] ?? null,
            'isError' => ($_GET['notice'] ?? '') === 'product-in-order'
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
        $product = [
            'id' => $id,
            'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => strtolower(trim((string) ($_POST['slug'] ?? ''))),
            'short_description' => trim((string) ($_POST['short_description'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')),
            'stock' => trim((string) ($_POST['stock'] ?? '')),
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
        $existingProduct = null;
        if ($id > 0) {
            $existing = $this->db->prepare('SELECT id FROM products WHERE id=? AND seller_id=?');
            $existing->execute([$id, $_SESSION['user']['id']]);
            $existingProduct = $existing->fetch();
            if (!$existingProduct) {
                http_response_code(404);
                renderLayout('Không tìm thấy sản phẩm', $this->views . '/404.php');
                return;
            }
        }
        $duplicate = $this->db->prepare('SELECT id FROM products WHERE slug=? AND id<>?');
        $duplicate->execute([$product['slug'], $id]);
        if ($duplicate->fetch()) {
            $this->renderSellerProductForm($product, 'Slug này đã được sản phẩm khác sử dụng.');
            return;
        }

        if ($id > 0) {
            $statement = $this->db->prepare('
                UPDATE products
                SET category_id=?, name=?, slug=?, short_description=?, description=?, price=?, stock=?
                WHERE id=? AND seller_id=?
            ');
            $statement->execute([
                $product['category_id'], $product['name'], $product['slug'],
                $product['short_description'], $product['description'], $price,
                $stock, $id, $_SESSION['user']['id']
            ]);
        } else {
            $statement = $this->db->prepare('
                INSERT INTO products(category_id,name,slug,short_description,description,price,stock,featured,seller_id)
                VALUES(?,?,?,?,?,?,?,0,?)
            ');
            $statement->execute([
                $product['category_id'], $product['name'], $product['slug'],
                $product['short_description'], $product['description'], $price,
                $stock, $_SESSION['user']['id']
            ]);
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
        $statement = $this->db->prepare('UPDATE order_items SET status=? WHERE id=? AND seller_id=?');
        $statement->execute([$status, $id, $_SESSION['user']['id']]);
        if ($statement->rowCount() === 0) {
            $check = $this->db->prepare('SELECT id FROM order_items WHERE id=? AND seller_id=?');
            $check->execute([$id, $_SESSION['user']['id']]);
            if (!$check->fetch()) {
                http_response_code(404);
                renderLayout('Không tìm thấy món trong đơn hàng', $this->views . '/404.php');
                return;
            }
        }
        header('Location: /seller?notice=status-saved');
        exit;
    }

    public function admin(): void
    {
        $this->requireAdmin();
        $stats = [
            'users' => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'products' => (int) $this->db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'orders' => (int) $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'contacts' => (int) $this->db->query("SELECT COUNT(*) FROM contacts WHERE status='unread'")->fetchColumn(),
        ];
        $orders = $this->db->query('SELECT * FROM orders ORDER BY id DESC LIMIT 10')->fetchAll();
        renderLayout('Quản trị', $this->views . '/admin.php', ['stats' => $stats, 'orders' => $orders]);
    }
}
