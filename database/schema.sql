-- ==========================================
-- SCRIPT TẠO DATABASE QUẢN LÝ SIÊU THỊ GIÀY
-- ==========================================

CREATE DATABASE IF NOT EXISTS shop_giay_admin
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE shop_giay_admin;

-- ==========================================
-- 1. BẢNG PHÂN QUYỀN VÀ NGƯỜI DÙNG (ADMIN/STAFF)
-- ==========================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE, -- Admin, Sales, Warehouse
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- ==========================================
-- 2. QUẢN LÝ KHÁCH HÀNG
-- ==========================================
CREATE TABLE customer_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE, -- New, VIP, Loyal...
    min_spent DECIMAL(15,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tier_id INT DEFAULT 1,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) UNIQUE,
    address TEXT,
    password VARCHAR(255), -- Cho phép KH đăng nhập nếu cần
    total_spent DECIMAL(15,2) DEFAULT 0,
    reward_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tier_id) REFERENCES customer_tiers(id)
);

-- ==========================================
-- 3. DANH MỤC VÀ THƯƠNG HIỆU
-- ==========================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    parent_id INT DEFAULT NULL,
    status BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    contact_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    logo VARCHAR(255),
    status BOOLEAN DEFAULT TRUE
);

-- ==========================================
-- 4. SẢN PHẨM VÀ BIẾN THỂ (SIZE/MÀU SẮC)
-- ==========================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    gender ENUM('Men', 'Women', 'Unisex', 'Kids') DEFAULT 'Unisex',
    description TEXT,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('In Stock', 'Out of Stock', 'Discontinued') DEFAULT 'In Stock',
    min_stock_level INT DEFAULT 5,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    
);

CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    color VARCHAR(50) NOT NULL,
    import_price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    image_url VARCHAR(255),
    UNIQUE(product_id, size, color),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ==========================================
-- 5. KHUYẾN MÃI (PROMOTIONS)
-- ==========================================
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    discount_type ENUM('Percent', 'Fixed Amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(15,2) DEFAULT 0,
    max_discount_amount DECIMAL(15,2) DEFAULT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    status BOOLEAN DEFAULT TRUE
);

-- ==========================================
-- 6. ĐƠN HÀNG (ORDERS)
-- ==========================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    user_id INT NULL, -- Nhân viên duyệt đơn
    promotion_id INT NULL,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    final_amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('COD', 'Bank Transfer', 'Credit Card') DEFAULT 'COD',
    payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
    order_status ENUM('Pending', 'Confirmed', 'Shipping', 'Completed', 'Cancelled') DEFAULT 'Pending',
    shipping_address TEXT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id)
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_variant_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
);

CREATE TABLE order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by INT, -- User ID
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- ==========================================
-- 7. QUẢN LÝ KHO (INVENTORY/RECEIPTS)
-- ==========================================
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    status BOOLEAN DEFAULT TRUE
);

CREATE TABLE inventory_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    user_id INT NOT NULL, -- Người nhập kho
    receipt_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE inventory_receipt_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    product_variant_id INT NOT NULL,
    quantity INT NOT NULL,
    import_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (receipt_id) REFERENCES inventory_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
);

-- ==========================================
-- 8. AUDIT LOG CHỨNG TỪ THEO DÕI LOG CHỈNH SỬA
-- ==========================================
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL, -- CREATE_PRODUCT, UPDATE_PRICE, APPROVE_ORDER
    table_name VARCHAR(50),
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    banner_image VARCHAR(255),
    start_date DATE,
    end_date DATE,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS collection_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE(collection_id, product_id)
);

-- ==========================================
-- TRIGGERS VÀ STORED PROCEDURES
-- ==========================================

DELIMITER $$

-- 1. Trigger trừ tồn kho khi đơn hàng được CONFIRMED hoặc COMPLETED (Tuỳ logic, ở đây chọn Confirmed)
CREATE TRIGGER trg_after_order_confirmed
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_status = 'Confirmed' AND OLD.order_status = 'Pending' THEN
        UPDATE product_variants pv
        JOIN order_details od ON pv.id = od.product_variant_id
        SET pv.stock_quantity = pv.stock_quantity - od.quantity
        WHERE od.order_id = NEW.id;
    END IF;
    
    -- Trả lại tồn kho nếu đơn bị huỷ (từ Confirmed)
    IF NEW.order_status = 'Cancelled' AND (OLD.order_status = 'Confirmed' OR OLD.order_status = 'Shipping') THEN
        UPDATE product_variants pv
        JOIN order_details od ON pv.id = od.product_variant_id
        SET pv.stock_quantity = pv.stock_quantity + od.quantity
        WHERE od.order_id = NEW.id;
    END IF;
    
    -- Cập nhật tổng chi tiêu cho KH khi hoàn thành
    IF NEW.order_status = 'Completed' AND OLD.order_status != 'Completed' THEN
        IF NEW.customer_id IS NOT NULL THEN
            UPDATE customers 
            SET total_spent = total_spent + NEW.final_amount
            WHERE id = NEW.customer_id;
        END IF;
    END IF;
END$$

-- 2. Trigger cộng tồn kho khi nhập hàng
CREATE TRIGGER trg_after_inventory_inserted
AFTER INSERT ON inventory_receipt_details
FOR EACH ROW
BEGIN
    UPDATE product_variants
    SET stock_quantity = stock_quantity + NEW.quantity,
        import_price = NEW.import_price -- (Option) Cập nhật giá nhập mới nhất
    WHERE id = NEW.product_variant_id;
END$$

-- 3. Procedure: Lấy DOANH THU THÔNG KÊ
CREATE PROCEDURE GetDashboardStats(IN p_start_date DATE, IN p_end_date DATE)
BEGIN
    -- Doanh thu
    SELECT SUM(final_amount) as total_revenue
    FROM orders 
    WHERE order_status = 'Completed' 
    AND DATE(created_at) BETWEEN p_start_date AND p_end_date;
    
    -- Đơn hàng
    SELECT COUNT(*) as total_orders
    FROM orders
    WHERE DATE(created_at) BETWEEN p_start_date AND p_end_date;
    
    -- Đơn huỷ
    SELECT COUNT(*) as cancelled_orders
    FROM orders
    WHERE order_status = 'Cancelled'
    AND DATE(created_at) BETWEEN p_start_date AND p_end_date;
END$$

DELIMITER ;

-- ==========================================
-- DỮ LIỆU MẪU (SEEDER CƠ BẢN)
-- ==========================================
INSERT INTO roles (name, description) VALUES 
('Admin', 'Toàn quyền hệ thống'),
('Sales', 'Nhân viên bán hàng, xử lý đơn'),
('Warehouse', 'Nhân viên kho');

INSERT INTO users (role_id, username, password, full_name, is_active) VALUES 
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1); -- pass: password

INSERT INTO customer_tiers (name, min_spent, discount_percent) VALUES
('New Member', 0, 0),
('Silver', 5000000, 2.0),
('Gold', 15000000, 5.0),
('VIP', 30000000, 10.0);

