<?php
// app/Controllers/CategoryController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class CategoryController extends Controller
{
    public function apiStore()    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $categoryModel = $this->model('Category');
        $auditModel = $this->model('AuditLog');

        $name = trim($_POST['name'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if ($name === '') {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Tên danh mục không được để trống'
            ], 400);
        }

        // Nếu có parent → kiểm tra tồn tại
        if ($parent_id) {
            $parent = $categoryModel->findById($parent_id);
            if (!$parent) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Danh mục cha không tồn tại'
                ], 400);
            }
        }

        $data = [
            'name' => $name,
            'slug' => $categoryModel->createSlug($name),
            'parent_id' => $parent_id,
            'status' => $status
        ];

        $id = $categoryModel->insert($data);

        if ($id) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1,
                'CREATE_CATEGORY', 'categories', $id, null, $data);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Thêm danh mục thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi lưu vào CSDL'
        ], 500);    }

    public function apiUpdate()    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);
        }

        $categoryModel = $this->model('Category');
        $auditModel = $this->model('AuditLog');

        $category = $categoryModel->findById($id);
        if (!$category) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Không tồn tại'], 404);
        }

        $name = trim($_POST['name'] ?? $category['name']);
        $parent_id = isset($_POST['parent_id'])
            ? (!empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null)
            : $category['parent_id'];

        $status = isset($_POST['status'])
            ? (int)$_POST['status']
            : $category['status'];

        // Không cho chọn chính nó làm cha
        if ($parent_id == $id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Danh mục không thể là cha của chính nó'
            ], 400);
        }

        // Kiểm tra danh mục cha tồn tại
        if ($parent_id) {
            $parent = $categoryModel->findById($parent_id);
            if (!$parent) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Danh mục cha không tồn tại'
                ], 400);
            }
        }

        $data = [
            'name' => $name,
            'slug' => $categoryModel->createSlug($name),
            'parent_id' => $parent_id,
            'status' => $status
        ];

        if ($categoryModel->update($id, $data)) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1,
                'UPDATE_CATEGORY', 'categories', $id, $category, $data);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Lỗi khi cập nhật'
        ], 500);    }

    public function apiDelete()    {
        AuthMiddleware::hasRole(['Admin']);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);
        }

        $categoryModel = $this->model('Category');
        $auditModel = $this->model('AuditLog');

        $category = $categoryModel->findById($id);
        if (!$category) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Không tồn tại'], 404);
        }

        // ❌ Kiểm tra có danh mục con không
        if ($categoryModel->hasChildren($id)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Không thể xóa vì còn danh mục con'
            ], 400);
        }

        // ❌ Kiểm tra có sản phẩm thuộc danh mục không
        if ($categoryModel->hasProducts($id)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Không thể xóa vì còn sản phẩm thuộc danh mục'
            ], 400);
        }

        if ($categoryModel->delete($id)) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1,
                'DELETE_CATEGORY', 'categories', $id, $category, null);

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
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID danh mục'], 400);
        }

        $categoryModel = $this->model('Category');
        $category = $categoryModel->findById($id);

        if ($category) {
            return $this->jsonResponse(['status' => 'success', 'data' => $category]);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy danh mục'], 404);
    }
}
