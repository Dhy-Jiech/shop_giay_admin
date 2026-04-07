<?php
// app/Controllers/SupplierController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class SupplierController extends Controller
{
    public function apiStore()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $supplierModel = $this->model('Supplier');
        $auditModel = $this->model('AuditLog');

        $name = $_POST['name'] ?? '';
        $contact_name = $_POST['contact_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $status = $_POST['status'] ?? 1;
        $logo_url = '';

        // Xử lý upload file logo (nếu có)
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = 'supp_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = '../public/uploads/suppliers/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newName)) {
                    $logo_url = '/shop_giay_admin/public/uploads/suppliers/' . $newName;
                }
            }
        }

        if (empty($name)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Tên nhà cung cấp không được để trống'], 400);
        }

        $data = [
            'name' => $name,
            'contact_name' => $contact_name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'logo' => $logo_url,
            'status' => $status
        ];

        $id = $supplierModel->insert($data);

        if ($id) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1, 'CREATE_SUPPLIER', 'suppliers', $id, null, $data);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Thêm nhà cung cấp thành công', 'id' => $id]);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi lưu vào CSDL'], 500);
    }

    public function apiUpdate()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Thiếu ID nhà cung cấp'
            ], 400);
        }

        $supplierModel = $this->model('Supplier');
        $auditModel = $this->model('AuditLog');

        $supplier = $supplierModel->findById($id);
        if (!$supplier) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Nhà cung cấp không tồn tại'
            ], 404);
        }

        $name = $_POST['name'] ?? $supplier['name'];
        $contact_name = $_POST['contact_name'] ?? $supplier['contact_name'];
        $phone = $_POST['phone'] ?? $supplier['phone'];
        $email = $_POST['email'] ?? $supplier['email'];
        $address = $_POST['address'] ?? $supplier['address'];
        $status = $_POST['status'] ?? $supplier['status'];
        $logo_url = $supplier['logo'];

        // Xử lý upload file logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = 'supp_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = '../public/uploads/suppliers/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newName)) {
                    $logo_url = '/shop_giay_admin/public/uploads/suppliers/' . $newName;
                }
            }
        }

        $data = [
            'name' => $name,
            'contact_name' => $contact_name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'logo' => $logo_url,
            'status' => $status
        ];

        if ($supplierModel->update($id, $data)) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1, 'UPDATE_SUPPLIER', 'suppliers', $id, $supplier, $data);
            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi cập nhật'
        ], 500);
    }

    public function apiDelete()
    {
        AuthMiddleware::hasRole(['Admin']);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Thiếu ID nhà cung cấp'
            ], 400);
        }

        $supplierModel = $this->model('Supplier');

        if ($supplierModel->delete($id)) {
            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi xóa'
        ], 500);
    }

    public function apiGet()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID nhà cung cấp'], 400);
        }

        $supplierModel = $this->model('Supplier');
        $supplier = $supplierModel->findById($id);

        if ($supplier) {
            return $this->jsonResponse(['status' => 'success', 'data' => $supplier]);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy nhà cung cấp'], 404);
    }
}
