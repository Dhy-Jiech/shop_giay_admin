<?php
// app/Controllers/PromotionController.php
require_once dirname(__DIR__) . '/Core/Controller.php';
require_once dirname(__DIR__) . '/Models/Promotion.php';
require_once dirname(__DIR__) . '/Middleware/AuthMiddleware.php';

class PromotionController extends Controller
{
    private $promotionModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->promotionModel = new Promotion();
    }

    public function index()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $promotions = $this->promotionModel->findAll();

        $this->view('admin/promotions', [
            'title' => 'Quản lý Khuyến Mãi',
            'promotions' => $promotions
        ]);
    }

    public function apiGet()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $id = $_GET['id'] ?? null;
        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Missing ID'], 400);

        $promo = $this->promotionModel->findById($id);
        if ($promo) {
            return $this->jsonResponse(['status' => 'success', 'data' => $promo]);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Promotion not found'], 404);
    }

    public function apiSave()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            $data = [
                'code' => trim($_POST['code']),
                'name' => trim($_POST['name']),
                'discount_type' => $_POST['discount_type'],
                'discount_value' => $_POST['discount_value'],
                'min_order_value' => $_POST['min_order_value'] ?: 0,
                'max_discount_amount' => $_POST['max_discount_amount'] ?: null,
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'usage_limit' => $_POST['usage_limit'] ?: null,
                'status' => isset($_POST['status']) ? 1 : 0
            ];

            if ($id) {
                // Update
                if ($this->promotionModel->updatePromo($id, $data)) {
                    return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật khuyến mãi thành công']);
                }
            }
            else {
                // Create
                // Check if code exists
                if ($this->promotionModel->findByCode($data['code'])) {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Mã giảm giá đã tồn tại'], 400);
                }
                if ($this->promotionModel->create($data)) {
                    return $this->jsonResponse(['status' => 'success', 'message' => 'Thêm khuyến mãi thành công']);
                }
            }
            return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi lưu dữ liệu'], 500);
        }
    }

    public function apiDelete()
    {
        AuthMiddleware::hasRole(['Admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_GET['id'] ?? null;
            if (!$id)
                return $this->jsonResponse(['status' => 'error', 'message' => 'Missing ID'], 400);

            if ($this->promotionModel->delete($id)) {
                return $this->jsonResponse(['status' => 'success', 'message' => 'Xóa khuyến mãi thành công']);
            }
            return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi xóa'], 500);
        }
    }

    public function apiToggleStatus()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_GET['id'] ?? null;
            if (!$id)
                return $this->jsonResponse(['status' => 'error', 'message' => 'Missing ID'], 400);

            if ($this->promotionModel->toggleStatus($id)) {
                return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
            }
            return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi cập nhật'], 500);
        }
    }

    protected function jsonResponse($data, $code = 200)
{
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}
}
