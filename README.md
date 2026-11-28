# FoodStore

[![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/docs/Web/JavaScript)

FoodStore là website thương mại điện tử bán thực phẩm và món ăn, được xây dựng bằng **PHP thuần, PDO và MySQL** theo mô hình MVC tự triển khai.

## Thành viên

- **Khách hàng:** HCMUT
- **Nhóm phát triển:**
  - [Nguyễn Minh Quốc](https://github.com/MnhQuoc) - quoc.nguyen851032@hcmut.edu.vn

## Mục lục

- [Tính năng](#tính-năng)
- [Công nghệ sử dụng](#công-nghệ-sử-dụng)
- [Cấu trúc thư mục](#cấu-trúc-thư-mục)
- [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
- [Cài đặt và chạy dự án](#cài-đặt-và-chạy-dự-án)
- [Cấu hình cơ sở dữ liệu](#cấu-hình-cơ-sở-dữ-liệu)
- [Phân quyền tài khoản](#phân-quyền-tài-khoản)
- [Các route chính](#các-route-chính)
- [Cơ sở dữ liệu](#cơ-sở-dữ-liệu)
- [SEO và bảo mật](#seo-và-bảo-mật)
- [Ghi chú phát triển](#ghi-chú-phát-triển)

## Tính năng

### Khách hàng

- Xem trang chủ, giới thiệu, bảng giá và câu hỏi thường gặp.
- Tìm kiếm, phân trang và xem chi tiết sản phẩm.
- Quản lý giỏ hàng trong phiên làm việc.
- Thanh toán COD, nhập tên, số điện thoại, địa chỉ và ghi chú.
- Đăng ký, đăng nhập, đăng xuất và cập nhật hồ sơ.
- Đổi mật khẩu và xem lịch sử đơn hàng.
- Xem tin tức, tìm kiếm bài viết và bình luận khi đã đăng nhập.

### Người bán (`seller`)

- Thêm, sửa, xoá và bật/tắt trạng thái bán của sản phẩm.
- Tải ảnh JPG, PNG hoặc WebP cho sản phẩm (tối đa 5 MB).
- Quản lý món trong đơn hàng và cập nhật trạng thái xử lý.
- Xem tổng quan đơn hàng, đơn chờ xử lý và doanh thu.
- Xem doanh thu theo ngày và theo tháng trong 30 ngày hoặc 12 tháng gần nhất.

### Quản trị viên (`admin`)

- Xem thống kê người dùng, sản phẩm, đơn hàng, bài viết và bình luận.
- Khoá/mở khoá thành viên.
- Bật/tắt sản phẩm nổi bật.
- Cập nhật trạng thái đơn hàng.
- Quản lý FAQ, bài viết, trạng thái bài viết và bình luận.
- Tạo, sửa và xoá bài viết.

### Kỹ thuật

- Bản đồ route MVC tự triển khai.
- PDO prepared statement và mật khẩu được mã hoá bằng `password_hash()`.
- CSRF token cho các biểu mẫu thay đổi dữ liệu.
- Phân trang danh sách sản phẩm, tin tức và khu vực quản trị.
- Sitemap XML và `robots.txt`.
- SEO meta tag, Open Graph và dữ liệu cấu trúc Schema.org.

## Công nghệ sử dụng

| Thành phần | Công nghệ |
| --- | --- |
| Backend | PHP 8+ |
| Cơ sở dữ liệu | MySQL 5.7+ hoặc MariaDB tương thích |
| Giao tiếp dữ liệu | PDO, prepared statement |
| Giao diện | HTML5, CSS3, JavaScript thuần |
| Máy chủ phát triển | PHP Built-in Server hoặc XAMPP/Apache |

Không sử dụng Composer hoặc framework JavaScript; dự án có thể chạy mà không cần cài thêm package.

## Cấu trúc thư mục

```text
FoodStorePHP/
├── app/
│   ├── controllers/
│   │   └── AppController.php
│   └── views/
│       ├── layout.php
│       ├── header.php
│       ├── footer.php
│       ├── home.php
│       ├── products.php
│       ├── product.php
│       ├── cart.php
│       ├── checkout.php
│       ├── login.php
│       ├── register.php
│       ├── profile.php
│       ├── change-password.php
│       ├── order-history.php
│       ├── seller.php
│       ├── seller-product-form.php
│       ├── seller-revenue.php
│       ├── admin.php
│       ├── admin-section.php
│       ├── admin-post-form.php
│       ├── news.php
│       ├── news-detail.php
│       ├── pricing.php
│       ├── faq.php
│       └── about.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── public/
│   ├── assets/
│   │   ├── css/style.css
│   │   ├── js/app.js
│   │   └── images/
│   ├── uploads/products/
│   ├── index.php
│   ├── router.php
│   └── .htaccess
└── README.md
```

## Yêu cầu hệ thống

- PHP 8 trở lên.
- PDO MySQL PHP extension.
- MySQL 5.7+ hoặc MariaDB tương thích.
- Apache/XAMPP hoặc PHP Built-in Server.
- Trình duyệt hiện đại.

Kiểm tra môi trường PHP:

```powershell
php -v
php -m
```

Trong kết quả `php -m` phải có `PDO` và `pdo_mysql`.

## Cài đặt và chạy dự án

### Cách 1: Chạy bằng PHP Built-in Server

Từ thư mục gốc của dự án:

```powershell
cd "C:\Users\nguye\OneDrive\Desktop\TaiLieuNam4\Lập trình Web\BTL\FoodStorePHP"
php -S 127.0.0.1:8099 -t public public/router.php
```

Sau đó truy cập:

```text
http://127.0.0.1:8099
```

### Cách 2: Chạy bằng XAMPP/Apache

1. Cài đặt và khởi động Apache cùng MySQL.
2. Đặt thư mục dự án trong `xampp\htdocs` hoặc cấu hình Apache trỏ DocumentRoot tới thư mục `public`.
3. Bật module `mod_rewrite` trong Apache.
4. Truy cập theo tên thư mục hoặc virtual host đã cấu hình.

Ví dụ nếu dự án nằm tại `C:\xampp\htdocs\FoodStorePHP`:

```text
http://localhost/FoodStorePHP/public
```

## Cấu hình cơ sở dữ liệu

### Tạo database

Khởi động MySQL và tạo database:

```sql
CREATE DATABASE btl_ltweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sau đó import toàn bộ file [`database/schema.sql`](database/schema.sql) bằng phpMyAdmin, MySQL CLI hoặc MySQL Workbench.

Cấu hình mặc định trong [`config/database.php`](config/database.php):

| Biến | Giá trị mặc định |
| --- | --- |
| `DB_HOST` | `127.0.0.1` |
| `DB_NAME` | `btl_ltweb` |
| `DB_USER` | `root` |
| `DB_PASS` | chuỗi rỗng |

Ghi đè cấu hình trong PowerShell trước khi khởi động server:

```powershell
$env:DB_HOST = "127.0.0.1"
$env:DB_NAME = "btl_ltweb"
$env:DB_USER = "root"
$env:DB_PASS = ""
php -S 127.0.0.1:8099 -t public public/router.php
```

> Khi chạy bằng XAMPP/Apache, cấu hình có thể được khai báo trong biến môi trường của hệ thống hoặc sửa trực tiếp trong `config/database.php`.

Khi ứng dụng kết nối lần đầu, hàm migrate trong `config/database.php` sẽ kiểm tra và bổ sung các cột, ràng buộc và bảng cần cho chức năng người bán và bài viết.

## Phân quyền tài khoản

Dự án có ba role:

- `user`: khách hàng mua hàng.
- `seller`: người bán sản phẩm và xử lý đơn.
- `admin`: quản trị toàn bộ dữ liệu.

Tài khoản đăng ký mới mặc định là `user`. Sau khi đăng ký trong ứng dụng, dùng phpMyAdmin hoặc SQL để cấp quyền:

```sql
UPDATE users
SET role = 'seller'
WHERE email = 'seller@example.com';
```

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@example.com';
```

Không có tài khoản admin/seller hoặc mật khẩu mặc định được cung cấp trong file SQL. Tài khoản phải được tạo bằng chức năng đăng ký trước, sau đó mới được đổi role.

## Các route chính

| Route | Mô tả |
| --- | --- |
| `/` | Trang chủ |
| `/about` | Giới thiệu |
| `/products` | Danh sách và tìm kiếm sản phẩm |
| `/products/{slug}` | Chi tiết sản phẩm |
| `/pricing` | Bảng giá |
| `/faq` | Câu hỏi thường gặp |
| `/news` | Danh sách tin tức |
| `/news/{slug}` | Chi tiết bài viết |
| `/cart` | Giỏ hàng |
| `/checkout` | Thanh toán |
| `/login`, `/register` | Đăng nhập và đăng ký |
| `/profile`, `/profile/password` | Hồ sơ và đổi mật khẩu |
| `/orders` | Lịch sử mua hàng |
| `/seller` | Khu vực người bán |
| `/seller/revenue` | Báo cáo doanh thu người bán |
| `/admin` | Trang tổng quan quản trị |
| `/admin/users` | Quản lý thành viên |
| `/admin/products` | Quản lý sản phẩm |
| `/admin/orders` | Quản lý đơn hàng |
| `/admin/faqs` | Quản lý FAQ |
| `/admin/posts` | Quản lý bài viết |
| `/admin/comments` | Quản lý bình luận |
| `/robots.txt`, `/sitemap.xml` | Hỗ trợ SEO |

## Cơ sở dữ liệu

Các bảng chính:

- `users`: tài khoản, hồ sơ và phân quyền.
- `categories`: danh mục sản phẩm.
- `products`: sản phẩm, tồn kho, ảnh và người bán.
- `orders`: đơn hàng.
- `order_items`: từng món trong đơn, người bán và trạng thái món.
- `faqs`: câu hỏi thường gặp.
- `posts`: bài viết tin tức.
- `post_comments`: bình luận bài viết.

Trạng thái đơn hàng/món hàng:

```text
pending -> confirmed -> shipping -> completed
                  \-> cancelled
```

## SEO và bảo mật

- CSRF token được tạo bằng `random_bytes()` và kiểm tra bằng `hash_equals()`.
- Mật khẩu được mã hoá bằng `password_hash()` và kiểm tra bằng `password_verify()`.
- Dữ liệu đầu vào được lọc và truy vấn qua PDO prepared statement.
- Ảnh sản phẩm được kiểm tra MIME, kích thước pixel, dung lượng và phần mở rộng.
- Giao diện dùng `htmlspecialchars` để xuất dữ liệu an toàn.
- Có canonical URL, meta description, Open Graph, Twitter Card và JSON-LD.
- Các khu vực admin/seller có kiểm tra đăng nhập và role.

## Ghi chú phát triển

- Session được dùng để lưu người dùng và giỏ hàng.
- Thư mục [`public/uploads/products/`](public/uploads/products/) lưu ảnh do người bán tải lên.
- Khi cấu hình server ngoài Windows/XAMPP, cần đảm bảo thư mục upload có quyền ghi.
- Khi thay đổi schema thủ công, nên sao lưu database trước khi import lại `schema.sql`.
- Với PHP Built-in Server, luôn đặt thư mục làm việc là thư mục gốc dự án và dùng `public/router.php`.
