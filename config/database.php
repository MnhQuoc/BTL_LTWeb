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

    if (!mysqlColumnExists($pdo, 'products', 'seller_id')) {
        $pdo->exec('ALTER TABLE products ADD seller_id INT UNSIGNED NULL, ADD INDEX idx_products_seller_id (seller_id), ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id)');
    }
    if (!mysqlColumnExists($pdo, 'order_items', 'seller_id')) {
        $pdo->exec('ALTER TABLE order_items ADD seller_id INT UNSIGNED NULL, ADD INDEX idx_order_items_seller_id (seller_id), ADD CONSTRAINT fk_order_items_seller FOREIGN KEY (seller_id) REFERENCES users(id)');
    }
    if (!mysqlColumnExists($pdo, 'order_items', 'status')) {
        $pdo->exec("ALTER TABLE order_items ADD status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
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
