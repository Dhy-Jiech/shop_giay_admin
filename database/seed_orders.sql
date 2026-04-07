-- Xóa dữ liệu cũ để tránh trùng lặp mã đơn
USE shop_giay_admin;
DELETE FROM order_status_history WHERE order_id IN (SELECT id FROM orders WHERE order_code IN ('ORD1001', 'ORD1002'));
DELETE FROM order_details WHERE order_id IN (SELECT id FROM orders WHERE order_code IN ('ORD1001', 'ORD1002'));
DELETE FROM orders WHERE order_code IN ('ORD1001', 'ORD1002');

-- 1. Tạo Khách hàng mẫu (nếu chưa có)
INSERT INTO customers (full_name, email, phone, address) 
SELECT 'Nguyễn Văn A', 'vana@example.com', '0987654321', '123 Đường ABC, Quận 1, TP.HCM'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE phone = '0987654321');

-- 2. Đơn hàng 1: Chờ duyệt (Pending)
SET @customer_a = (SELECT id FROM customers WHERE phone = '0987654321' LIMIT 1);
SET @variant_1 = (SELECT id FROM product_variants LIMIT 1);
SET @price_1 = (SELECT sale_price FROM product_variants WHERE id = @variant_1);
SET @discount_1 = 20000;

INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, total_amount, discount_amount, final_amount, shipping_address, order_status, payment_status, created_at)
VALUES ('ORD1003', @customer_a, 'Nguyễn Văn A', '0987654321', @price_1, @discount_1, @price_1 - @discount_1, '123 Đường ABC, Quận 1, TP.HCM', 'Pending', 'Pending', NOW());

SET @order_1 = LAST_INSERT_ID();
INSERT INTO order_details (order_id, product_variant_id, quantity, unit_price, total_price)
VALUES (@order_1, @variant_1, 1, @price_1, @price_1);

-- 3. Đơn hàng 2: Đã duyệt (Confirmed)
SET @variant_2 = (SELECT id FROM product_variants ORDER BY id DESC LIMIT 1);
SET @price_2 = (SELECT sale_price FROM product_variants WHERE id = @variant_2);
SET @qty_2 = 2;
SET @total_2 = @price_2 * @qty_2;

INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, total_amount, discount_amount, final_amount, shipping_address, order_status, payment_status, created_at)
VALUES ('ORD1002', @customer_a, 'Nguyễn Văn A', '0987654321', @total_2, 0, @total_2, '123 Đường ABC, Quận 1, TP.HCM', 'Confirmed', 'Paid', NOW() - INTERVAL 1 DAY);

SET @order_2 = LAST_INSERT_ID();
INSERT INTO order_details (order_id, product_variant_id, quantity, unit_price, total_price)
VALUES (@order_2, @variant_2, @qty_2, @price_2, @total_2);

-- 4. Lịch sử trạng thái
INSERT INTO order_status_history (order_id, status, note, changed_by) VALUES (@order_1, 'Pending', 'Khách hàng đặt hàng online', NULL);
INSERT INTO order_status_history (order_id, status, note, changed_by) VALUES (@order_2, 'Pending', 'Khách hàng đặt hàng online', NULL);
INSERT INTO order_status_history (order_id, status, note, changed_by) VALUES (@order_2, 'Confirmed', 'Admin đã duyệt đơn', 1);
