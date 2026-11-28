DROP TABLE IF EXISTS post_comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS contacts;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    avatar VARCHAR(255),
    role ENUM('user','admin','seller') NOT NULL DEFAULT 'user',
    blocked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    short_description VARCHAR(255),
    description TEXT,
    price INT UNSIGNED NOT NULL,
    image VARCHAR(255),
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    seller_id INT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    note TEXT,
    total INT UNSIGNED NOT NULL,
    status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED,
    name VARCHAR(150) NOT NULL,
    price INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED,
    status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS faqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS post_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    content TEXT NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

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
WHERE NOT EXISTS (SELECT 1 FROM posts existing WHERE existing.slug = sample.slug);

INSERT INTO faqs (question, answer, is_active)
SELECT sample.question, sample.answer, 1
FROM (
    SELECT 'Làm thế nào để đặt hàng?' AS question,
           'Chọn sản phẩm, thêm vào giỏ hàng, sau đó điền thông tin giao hàng và xác nhận đơn.' AS answer
    UNION ALL SELECT 'Tôi có thể thanh toán bằng cách nào?',
           'Các phương thức thanh toán khả dụng sẽ được hiển thị tại bước thanh toán.'
    UNION ALL SELECT 'Khi nào đơn hàng được giao?',
           'Thời gian giao dự kiến phụ thuộc vào địa chỉ và sẽ được thông báo khi xác nhận đơn.'
    UNION ALL SELECT 'Tôi muốn thay đổi hoặc hủy đơn hàng thì làm sao?',
           'Hãy liên hệ cửa hàng sớm nhất có thể và cung cấp thông tin đơn hàng để được hỗ trợ.'
    UNION ALL SELECT 'Làm sao để hỏi về thành phần hoặc dị ứng thực phẩm?',
           'Vui lòng liên hệ cửa hàng trước khi đặt món để xác nhận thành phần và lưu ý dị ứng.'
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM faqs existing WHERE existing.question = sample.question
);
