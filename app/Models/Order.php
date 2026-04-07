<?php
// app/Models/Order.php

class Order extends Model
{
    protected $table = 'orders';

    public function findAll()
    {
        return $this->searchAndFilter([]);
    }

    public function searchAndFilter($params = [])
    {
        $sql = "SELECT o.*, c.full_name as customer_name, c.phone as customer_phone 
                FROM {$this->table} o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                WHERE 1=1";
        $bindParams = [];

        if (!empty($params['search'])) {
            $sql .= " AND (
        o.order_code LIKE :search1 
        OR c.full_name LIKE :search2 
        OR c.phone LIKE :search3
    )";

            $bindParams[':search1'] = '%' . $params['search'] . '%';
            $bindParams[':search2'] = '%' . $params['search'] . '%';
            $bindParams[':search3'] = '%' . $params['search'] . '%';
        }

        if (!empty($params['status']) && $params['status'] !== 'All') {
            $sql .= " AND o.order_status = :status";
            $bindParams[':status'] = $params['status'];
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindParams);
        return $stmt->fetchAll();
    }

    // Lấy danh sách đơn hàng của khách hàng
    public function getByCustomerId($customer_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }

    // Lấy gợi ý cho tìm kiếm (Autocomplete)
    public function searchSuggestions($keyword)
    {
        $sql = "SELECT o.id, o.order_code, c.full_name as customer_name, c.phone as customer_phone
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.order_code LIKE ?
            OR c.full_name LIKE ?
            OR c.phone LIKE ?
            ORDER BY o.created_at DESC
            LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $like = "%$keyword%";
        $stmt->execute([$like, $like, $like]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết đơn hàng
    public function getDetails($order_id)
    {
        $sql = "SELECT od.*, pv.size, pv.color, p.name as product_name, pv.sku
                FROM order_details od
                JOIN product_variants pv ON od.product_variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                WHERE od.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll();
    }

    // Cập nhật trạng thái và ghi lịch sử (Sử dụng Transaction)
    public function updateStatus($order_id, $new_status, $user_id = null, $note = '')
    {
        try {
            $this->db->beginTransaction();

            // Cập nhật trạng thái chính
            $this->update($order_id, ['order_status' => $new_status]);

            // Ghi vào lịch sử thay đổi
            $stmt = $this->db->prepare("INSERT INTO order_status_history (order_id, status, changed_by, note) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $new_status, $user_id, $note]);

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Cập nhật trạng thái thanh toán và ghi lịch sử
    public function updatePaymentStatus($order_id, $new_status, $user_id = null, $note = '')
    {
        try {
            $this->db->beginTransaction();

            // Cập nhật trạng thái thanh toán
            $this->update($order_id, ['payment_status' => $new_status]);

            // Ghi vào lịch sử thay đổi
            $stmt = $this->db->prepare("INSERT INTO order_status_history (order_id, status, changed_by, note) VALUES (?, ?, ?, ?)");
            // Status ở đây mình ghi rõ là "Payment: [Status]" để dễ phân biệt
            $historyStatus = "Payment: " . $new_status;
            $stmt->execute([$order_id, $historyStatus, $user_id, $note]);

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Thống kê doanh thu 7 ngày qua
    public function getRevenueLast7Days()
    {
        $sql = "SELECT DATE(created_at) as date, SUM(final_amount) as revenue
                FROM orders
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND order_status != 'Cancelled'
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at)";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top sản phẩm bán chạy
    public function getTopSellingProducts()
    {
        $sql = "SELECT p.name, SUM(od.quantity) as total_sold
                FROM order_details od
                JOIN product_variants pv ON od.product_variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT 5";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thống kê trạng thái đơn hàng
    public function getOrderStatusStats()
    {
        $sql = "SELECT order_status, COUNT(*) as total FROM orders GROUP BY order_status";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy doanh thu theo khoảng thời gian (Sử dụng Stored Procedure)
    public function getRevenueByDateRange($startDate, $endDate)
    {
        $sql = "SELECT 
                SUM(CASE WHEN order_status != 'Cancelled' THEN final_amount ELSE 0 END) as total_revenue,
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }

    // Lấy doanh thu theo tháng (6 tháng gần nhất)
    public function getMonthlyRevenue()
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month, 
                    SUM(final_amount) as revenue
                FROM orders
                WHERE order_status IN ('Completed', 'Confirmed', 'Shipping')
                GROUP BY month
                ORDER BY month DESC
                LIMIT 6";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
