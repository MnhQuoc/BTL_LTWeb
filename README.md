# BTL Lập Trình Web - Nhóm 

- Khách hàng: HCMUT
- Mentor:
- Develop Team:
  - [Nguyễn Minh Quốc](https://github.com/MnhQuoc) (nguyenminhquoc812005@gmail.com)
  - 
  - 

## Giới thiệu

FoodStore là website thương mại điện tử bán đồ ăn, được xây dựng bằng PHP thuần, PDO và MySQL theo mô hình MVC tự triển khai.

## Công nghệ

- Backend: PHP 8
- Cơ sở dữ liệu: MySQL
- Kiến trúc: MVC tự triển khai
- Giao diện: HTML, CSS, JavaScript
- Database client: PDO

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
│       ├── cart.php
│       ├── checkout.php
│       ├── login.php
│       ├── register.php
│       ├── profile.php
│       ├── admin.php
│       └── seller.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── index.php
│   ├── router.php
│   └── .htaccess
└── README.md
```

## Tính năng

- Trang chủ, giới thiệu, sản phẩm, bảng giá, tin tức, FAQ và liên hệ
- Tìm kiếm, xem chi tiết và phân trang sản phẩm
- Giỏ hàng và đặt hàng COD
- Đăng ký, đăng nhập, đăng xuất và cập nhật tài khoản
- Phân quyền `user`, `admin` và `seller`
- Seller quản lý sản phẩm và trạng thái món trong đơn hàng của mình
- Admin xem thống kê và quản lý đơn hàng
- Mã hoá mật khẩu bằng `password_hash()`
- Sử dụng CSRF token và truy vấn PDO prepared statement

## Cài đặt

### Yêu cầu

- PHP 8 trở lên
- MySQL
- XAMPP hoặc môi trường PHP/MySQL tương đương

### Cấu hình cơ sở dữ liệu

Khởi động MySQL, sau đó tạo hoặc chọn database `btl_ltweb` trong phpMyAdmin. Import file [database/schema.sql](database/schema.sql) để tạo cấu trúc bảng.

Cấu hình mặc định:

```text
Host: 127.0.0.1
Database: btl_ltweb
User: root
Password: (trống)
```

Có thể ghi đè cấu hình bằng biến môi trường trên Windows PowerShell:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_NAME="btl_ltweb"
$env:DB_USER="root"
$env:DB_PASS=""
```

### Chạy dự án

```powershell
cd "C:\Users\nguye\OneDrive\Desktop\TaiLieuNam4\Lập trình Web\BTL\FoodStorePHP\public"
php -S localhost:8000 router.php
```

Sau đó truy cập:

```text
http://localhost:8000
```

### Cấp quyền Seller

Tài khoản đăng ký mới mặc định có role `user`. Có thể cập nhật role trong phpMyAdmin bằng truy vấn SQL:

```sql
UPDATE users
SET role = 'seller'
WHERE email = 'seller@example.com';
```
