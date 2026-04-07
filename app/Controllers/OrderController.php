<?php
// app/Controllers/OrderController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class OrderController extends Controller
{
    // API Lấy chi tiết đơn hàng (bao gồm SP và Timeline)
    public function apiDetail()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $id = $_GET['id'] ?? null;
        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);

        $orderModel = $this->model('Order');
        $historyModel = $this->model('OrderStatusHistory');

        $order = $orderModel->findById($id);
        if (!$order) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Lấy danh sách sản phẩm
        $items = $orderModel->getDetails($id);

        // Lấy lịch sử trạng thái
        $history = $historyModel->getByOrder($id);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'items' => $items,
                'history' => $history
            ]
        ]);
    }

    // API Duyệt đơn hàng (Confirmed)
    public function apiApprove($id)
    {
        // Giữ lại tích hợp với code cũ nhưng có thể dùng apiUpdateStatus thay thế
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');

        $order = $orderModel->findById($id);
        if ($order && $order['order_status'] === 'Pending') {
            $success = $orderModel->updateStatus($id, 'Confirmed', $_SESSION['user_id'] ?? 1, 'Duyệt đơn hàng');
            if ($success) {
                if (!empty($order['promotion_id'])) {
                    $promoModel = $this->model('Promotion');
                    $promoModel->incrementUsedCount($order['promotion_id']);
                }
                return $this->jsonResponse(['status' => 'success', 'message' => 'Duyệt đơn hàng thành công']);
            }
        }
        else {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Đơn hàng không ở trạng thái chờ duyệt'], 400);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi duyệt đơn'], 500);
    }

    // API Hủy đơn hàng
    public function apiCancel($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');

        $success = $orderModel->updateStatus($id, 'Cancelled', $_SESSION['user_id'] ?? 1, 'Hủy đơn hàng');

        if ($success) {
            return $this->jsonResponse(['status' => 'success', 'message' => 'Đã hủy đơn hàng']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi hủy đơn'], 500);
    }

    // API Cập nhật trạng thái chung
    public function apiUpdateStatus()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $id = $_GET['id'] ?? null;
        $status = $_POST['status'] ?? null;
        $note = $_POST['note'] ?? 'Cập nhật trạng thái';

        if (!$id || !$status)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu dữ liệu'], 400);

        $orderModel = $this->model('Order');
        $order = $orderModel->findById($id);
        if (!$order)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy đơn'], 404);

        $oldStatus = $order['order_status'];
        $success = $orderModel->updateStatus($id, $status, $_SESSION['user_id'] ?? 1, $note);

        if ($success) {
            // Nếu chuyển sang Confirmed từ trạng thái khác (thường là Pending)
            if ($status === 'Confirmed' && $oldStatus !== 'Confirmed') {
                if (!empty($order['promotion_id'])) {
                    $promoModel = $this->model('Promotion');
                    $promoModel->incrementUsedCount($order['promotion_id']);
                }
            }
            return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi cập nhật trạng thái'], 500);
    }

    // API Cập nhật trạng thái thanh toán
    public function apiUpdatePaymentStatus()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $id = $_GET['id'] ?? null;
        $status = $_POST['status'] ?? null;
        $note = $_POST['note'] ?? 'Cập nhật trạng thái thanh toán';

        if (!$id || !$status)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu dữ liệu'], 400);

        $orderModel = $this->model('Order');
        $order = $orderModel->findById($id);
        if (!$order)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy đơn'], 404);

        $success = $orderModel->updatePaymentStatus($id, $status, $_SESSION['user_id'] ?? 1, $note);

        if ($success) {
            return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật trạng thái thanh toán thành công']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi cập nhật trạng thái thanh toán'], 500);
    }

    public function apiDelete($id)
    {
        AuthMiddleware::hasRole(['Admin']); // Chỉ Admin mới được xóa
        $orderModel = $this->model('Order');
        if ($orderModel->delete($id)) {
            return $this->jsonResponse(['status' => 'success', 'message' => 'Xóa đơn hàng thành công']);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi xóa đơn hàng'], 500);
    }

    public function apiSearchSuggestions()
    {
        $keyword = $_GET['keyword'] ?? '';

        $model = $this->model('Order'); // SỬA CHỖ NÀY
        $data = $model->searchSuggestions($keyword);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function apiCustomerHistory($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');
        $history = $orderModel->getByCustomerId($id);
        return $this->jsonResponse(['status' => 'success', 'data' => $history]);
    }

    public function printInvoice($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');

        $order = $orderModel->findById($id);
        if (!$order) {
            die("Đơn hàng không tồn tại");
        }

        $items = $orderModel->getDetails($id);

        $this->view('admin/order_invoice', [
            'order' => $order,
            'items' => $items,
            'title' => 'In Hóa Đơn - ' . $order['order_code']
        ]);
    }
}
