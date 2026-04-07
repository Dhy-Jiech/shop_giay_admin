<?php
// app/Controllers/CustomerController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class CustomerController extends Controller
{
    // API Lấy danh sách khách hàng kèm hạng
    public function apiGetList()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $customerModel = $this->model('Customer');

        $sql = "SELECT c.*, ct.name as tier_name, ct.discount_percent 
                FROM customers c 
                LEFT JOIN customer_tiers ct ON c.tier_id = ct.id";
        $customers = $customerModel->getDb()->query($sql)->fetchAll();

        return $this->jsonResponse(['status' => 'success', 'data' => $customers]);
    }

    // API Lấy thông tin 1 khách hàng
    public function apiGet($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $customerModel = $this->model('Customer');
        $customer = $customerModel->findById($id);

        if ($customer) {
            return $this->jsonResponse(['status' => 'success', 'data' => $customer]);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Khách hàng không tồn tại']);
    }

    // API Cập nhật thông tin khách hàng
    public function apiUpdate($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $customerModel = $this->model('Customer');

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        // Validate basic
        if (empty($data['full_name']) || empty($data['phone'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Họ tên và SĐT là bắt buộc']);
        }

        if ($customerModel->update($id, $data)) {
            return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật thông tin thành công']);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Cập nhật thất bại hoặc không có thay đổi']);
    }

    // API Lấy lịch sử đơn hàng
    public function apiGetHistory($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');
        $orders = $orderModel->getByCustomerId($id);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $orders,
            'debug' => [
                'customer_id' => $id,
                'count' => count($orders)
            ]
        ]);
    }

    // API Cập nhật hạng thành viên thủ công
    public function apiUpdateTier($id)
    {
        AuthMiddleware::hasRole(['Admin']);
        $customerModel = $this->model('Customer');

        if ($customerModel->updateTier($id)) {
            return $this->jsonResponse(['status' => 'success', 'message' => 'Đã cập nhật hạng thành viên']);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Không có thay đổi hoặc khách hàng không tồn tại']);
    }
}
