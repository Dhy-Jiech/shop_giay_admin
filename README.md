# SHOE SUPERSTORE - E-COMMERCE ADMIN SYSTEM
***Phiên bản PHP MVC Thuần***

Đây là hệ thống quản lý siêu thị giày trực tuyến, được thiết kế theo mô hình MVC sử dụng PHP thuần và MySQL. Hệ thống đáp ứng các nhu cầu từ quản lý cơ bản tới nâng cao (Cảnh báo tồn kho, AI gợi ý cơ bản, Biểu đồ thống kê).

## ✨ TÍNH NĂNG NỔI BẬT

### I. Quản Lý Cơ Bản
1. **Sản phẩm & Biến thể**: Quản lý giày, phân loại theo size, màu sắc, kiểm tra tồn kho (Còn hàng, Hết hàng).
2. **Đơn hàng**: Quản lý lịch sử thay đổi trạng thái, tự động trừ tồn kho ngay khi xác nhận đơn qua **SQL Trigger**.
3. **Khách hàng**: Phân hạng (New, VIP...) dựa vào tổng chi tiêu.
4. **Khuyến mãi**: Giảm giá, giới hạn ngân sách.
5. **Nội dung web**: Dynamic API lấy sản phẩm nổi bật đẩy lên view/home.

### II. Quản Lý Nâng Cao
1. **Dashboard Thống Kê**: Tích hợp Chart.js, Stored Procedure truy xuất dữ liệu tối ưu.
2. **Quản Lý Phân Quyền (RBAC)**: AuthMiddleware bảo vệ URL (Admin, Sales, Warehouse).
3. **Dấu Vết Hệ Thống (Audit Log)**: Ghi lại từng thao tác sửa/xóa/duyệt đơn.
4. **Quản Lý Nhập Kho**: Cộng dồn số lượng trực tiếp qua SQL Trigger.

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT (MÔI TRƯỜNG XAMPP)

1. Mở **XAMPP Control Panel**, bật `Apache` và `MySQL`.
2. Mở trình duyệt web truy cập `http://localhost/phpmyadmin/`.
3. Nhập dữ liệu tự động hoặc bằng tay:
   - Trong ứng dụng, hệ thống đã nạp file `database/schema.sql` vào database `shop_giay_admin`.
   - File này chứa Script tạo bảng, Triggers và Procedures cùng Dữ liệu mẫu ban đầu.
4. Truy cập URL:
   - Dashboard Admin: `http://localhost/shop_giay_admin/public/?url=dashboard/index`
   - Hoặc có thể cấu hình Document Root thẳng vào thư mục `public` của dự án.
5. *(Tùy chọn nâng cao)*: Cấu hình Virtual Host của XAMPP để sử dụng URL như `http://shop_giay.local/`.

---

## 📁 CẤU TRÚC THƯ MỤC CƠ BẢN

```text
htdocs/shop_giay_admin/
├── app/
│   ├── Controllers/    # Điều hướng logic (Dashboard, Product, Order)
│   ├── Core/           # Lõi ứng dụng (Controller, Model, Database kết nối PDO)
│   ├── Middleware/     # Phân quyền Request (AuthMiddleware)
│   ├── Models/         # Truy vấn DB, xử lý Logic
│   └── Views/          # HTML/PHP giao diện (Blade-like rendering)
├── config/
│   └── database.php    # Kết nối MySQL (thay đổi mật khẩu root tại đây)
├── database/
│   └── schema.sql      # Script tạo Database + Table + Trigger + Procedure
├── public/
│   ├── index.php       # Entry Point & Simple Router
│   └── .htaccess       # URL Rewrite rules
└── README.md
```

## 🔐 THAY ĐỔI / NÂNG CẤP TRONG TƯƠNG LAI
- Phân tách Route ra tệp `routes/web.php` riêng.
- Tích hợp thêm Payment Gateway (VNPAY / MoMo).
- Thêm Cronjob để tắt Flash Sale khi hết hạn thời gian thực tế.
