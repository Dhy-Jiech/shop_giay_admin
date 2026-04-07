<?php
// app/Controllers/BrandController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class BrandController extends Controller
{
    public function apiStore()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $brandModel = $this->model('Brand');
        $auditModel = $this->model('AuditLog');

        $name = $_POST['name'] ?? '';
        $status = $_POST['status'] ?? 1;
        $logo_url = '';

        // Xử lý upload file
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = 'brand_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = '../public/uploads/brands/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newName)) {
                    $logo_url = '/shop_giay_admin/public/uploads/brands/' . $newName;
                }
            }
        }

        if (empty($name)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Tên thương hiệu không được để trống'], 400);
        }

        $data = [
            'name' => $name,
            'logo' => $logo_url,
            'status' => $status
        ];

        $id = $brandModel->insert($data);

        if ($id) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1, 'CREATE_BRAND', 'brands', $id, null, $data);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Thêm thương hiệu thành công', 'id' => $id]);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi lưu vào CSDL'], 500);
    }
    public function apiUpdate()    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Thiếu ID thương hiệu'
            ], 400);
        }

        $brandModel = $this->model('Brand');
        $auditModel = $this->model('AuditLog');

        $brand = $brandModel->findById($id);
        if (!$brand) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Thương hiệu không tồn tại'
            ], 404);
        }

        $name = $_POST['name'] ?? $brand['name'];
        $status = $_POST['status'] ?? $brand['status'];
        $logo_url = $brand['logo'];

        // xử lý upload giống bạn đã viết...

        $data = [
            'name' => $name,
            'logo' => $logo_url,
            'status' => $status
        ];

        if ($brandModel->update($id, $data)) {
            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi cập nhật'
        ], 500);    }    public function apiDelete()    {
        AuthMiddleware::hasRole(['Admin']);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Thiếu ID thương hiệu'
            ], 400);
        }

        $brandModel = $this->model('Brand');

        if ($brandModel->delete($id)) {
            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi xóa'
        ], 500);    }
    public function apiGet()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID thương hiệu'], 400);
        }

        $brandModel = $this->model('Brand');
        $brand = $brandModel->findById($id);

        if ($brand) {
            return $this->jsonResponse(['status' => 'success', 'data' => $brand]);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy thương hiệu'], 404);
    }
}
