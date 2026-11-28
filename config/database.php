<?php
declare(strict_types=1);

function database(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'btl_ltweb';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    migrateMysqlSellerSchema($pdo);
    return $pdo;
}

function migrateMysqlSellerSchema(PDO $pdo): void
{
    $roleType = $pdo->query("
        SELECT COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'
    ")->fetchColumn();
    if (is_string($roleType) && strpos($roleType, "'seller'") === false) {
        $pdo->exec("ALTER TABLE users MODIFY role ENUM('user','admin','seller') NOT NULL DEFAULT 'user'");
    }

    if (!mysqlColumnExists($pdo, 'users', 'address')) {
        $pdo->exec('ALTER TABLE users ADD address VARCHAR(255) NULL');
    }
    if (!mysqlColumnExists($pdo, 'products', 'seller_id')) {
        $pdo->exec('ALTER TABLE products ADD seller_id INT UNSIGNED NULL, ADD INDEX idx_products_seller_id (seller_id), ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id)');
    }
    if (!mysqlColumnExists($pdo, 'products', 'is_available')) {
        $pdo->exec('ALTER TABLE products ADD is_available TINYINT(1) NOT NULL DEFAULT 1');
    }
    if (!mysqlColumnExists($pdo, 'order_items', 'seller_id')) {
        $pdo->exec('ALTER TABLE order_items ADD seller_id INT UNSIGNED NULL, ADD INDEX idx_order_items_seller_id (seller_id), ADD CONSTRAINT fk_order_items_seller FOREIGN KEY (seller_id) REFERENCES users(id)');
    }
    if (!mysqlColumnExists($pdo, 'order_items', 'status')) {
        $pdo->exec("ALTER TABLE order_items ADD status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt VARCHAR(500),
            content TEXT NOT NULL,
            image VARCHAR(255),
            author_id INT UNSIGNED,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_comments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED,
            content TEXT NOT NULL,
            is_approved TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        INSERT INTO posts(title,slug,excerpt,content,is_published,published_at)
        SELECT sample.title,sample.slug,sample.excerpt,sample.content,1,CURRENT_TIMESTAMP
        FROM (
            SELECT 'Cách chọn thực phẩm tươi ngon mỗi ngày' AS title,
                   'chon-thuc-pham-tuoi-ngon-moi-ngay' AS slug,
                   'Những mẹo đơn giản để lựa chọn rau củ, thịt cá và nguyên liệu tươi ngon.' AS excerpt,
                   'Khi mua thực phẩm, hãy ưu tiên nguyên liệu có màu sắc tự nhiên, mùi thơm đặc trưng và được bảo quản đúng nhiệt độ. Rau củ nên còn tươi, không dập nát; thịt cá phải có độ đàn hồi tốt và không có mùi lạ.' AS content
            UNION ALL SELECT 'Bí quyết bữa trưa đầy đủ dinh dưỡng',
                   'bi-quyet-bua-trua-day-du-dinh-duong',
                   'Cân bằng tinh bột, đạm, rau xanh và chất béo tốt trong một bữa trưa tiện lợi.',
                   'Một bữa trưa đủ chất có thể gồm cơm hoặc khoai, một phần protein, nhiều loại rau và trái cây. Hãy hạn chế dầu mỡ, uống đủ nước và chọn khẩu phần phù hợp để duy trì năng lượng cả buổi chiều.'
            UNION ALL SELECT 'FoodStore ra mắt giao hàng trong 30 phút',
                   'foodstore-ra-mat-giao-hang-trong-30-phut',
                   'Dịch vụ giao nhanh mới giúp món ăn nóng hổi đến tay bạn sớm hơn.',
                   'FoodStore mở rộng khu vực phục vụ giao hàng nhanh trong bán kính gần cửa hàng. Khi đơn được xác nhận, bộ phận chế biến và giao hàng sẽ phối hợp để món ăn đến tay khách hàng nhanh và an toàn.'
        ) AS sample
        WHERE NOT EXISTS (SELECT 1 FROM posts existing WHERE existing.slug = sample.slug)
    ");
    $pdo->exec('DROP TABLE IF EXISTS contacts');
    $pdo->exec("
        UPDATE order_items oi
        INNER JOIN products p ON p.id = oi.product_id
        SET oi.seller_id = p.seller_id
        WHERE oi.seller_id IS NULL AND p.seller_id IS NOT NULL
    ");
}

function mysqlColumnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $statement->execute([$table, $column]);
    return (int) $statement->fetchColumn() > 0;
}
