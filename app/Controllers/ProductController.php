<?php
// app/Controllers/ProductController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class ProductController extends Controller
{

    // API Lấy danh sách sản phẩm
    public function apiGetList()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales', 'Warehouse']);

        $productModel = $this->model('Product');
        $products = $productModel->getProductsWithDetails(50, 0);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $products
        ]);
    }

    // Lưu sản phẩm mới kèm đa biến thể
    public function apiStore()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $productModel = $this->model('Product');
        $auditModel = $this->model('AuditLog');

        // 1. Thu thập thông tin sản phẩm chính
        $productData = [
            'category_id' => $_POST['category_id'] ?? null,
            'name' => $_POST['name'] ?? '',
            'gender' => $_POST['gender'] ?? 'Unisex',
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'In Stock',
            'is_featured' => isset($_POST['is_featured']) ? (int)$_POST['is_featured'] : 0,
        ];

        // 2. Xử lý upload ảnh chính (Primary Image)
        $primaryImageUrl = null;
        if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['primary_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = '../public/uploads/products/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0777, true);

                if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $uploadDir . $newName)) {
                    $primaryImageUrl = '/shop_giay_admin/public/uploads/products/' . $newName;
                }
            }
        }

        // 3. Xử lý danh sách biến thể
        $variants = [];
        if (isset($_POST['variants']['size'])) {

            $sizes = $_POST['variants']['size'];
            $colors = $_POST['variants']['color'];
            $skus = $_POST['variants']['sku'];
            $imports = $_POST['variants']['import_price'];
            $sales = $_POST['variants']['sale_price'];
            $stocks = $_POST['variants']['stock'];

            for ($i = 0; $i < count($sizes); $i++) {

                $variants[] = [
                    'size' => $sizes[$i],
                    'color' => $colors[$i],
                    'import_price' => $imports[$i],
                    'sale_price' => $sales[$i],
                    'stock_quantity' => $stocks[$i],
                    'sku' => !empty($skus[$i])
                    ? $skus[$i]
                    : strtoupper(substr($productData['name'], 0, 3)) . '-' . $sizes[$i] . rand(10, 99)
                ];
            }
        }

        // 4. Lưu vào CSDL qua Model (Dùng transaction trong model)
        $productId = $productModel->createWithVariants($productData, $variants);

        if ($productId) {
            // Lưu ảnh vào bảng product_images
            if ($primaryImageUrl) {
                $productModel->setPrimaryImage($productId, $primaryImageUrl);
            }

            $auditModel->logAction($_SESSION['user_id'] ?? 1, 'CREATE_PRODUCT_MULTI', 'products', $productId, null, ['product' => $productData, 'variants' => $variants]);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Thêm sản phẩm và biến thể thành công']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi lưu sản phẩm'], 500);
    }

    // API Lấy biến thể của một sản phẩm
    public function apiGetVariants()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales', 'Warehouse']);
        $id = $_GET['id'] ?? null;
        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);

        $variantModel = $this->model('ProductVariant');
        $variants = $variantModel->getByProduct($id);

        return $this->jsonResponse(['status' => 'success', 'data' => $variants]);
    }

    // API Lấy chi tiết sản phẩm (cho sửa)
    public function apiGetDetail()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales', 'Warehouse']);
        $id = $_GET['id'] ?? null;
        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);

        $productModel = $this->model('Product');
        $product = $productModel->getFullDetail($id);

        if ($product) {
            return $this->jsonResponse(['status' => 'success', 'data' => $product]);
        }
        return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm'], 404);
    }

    // Cập nhật sản phẩm
    public function apiUpdate($id = null)
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);

        $productModel = $this->model('Product');
        $auditModel = $this->model('AuditLog');

        $oldData = $productModel->getFullDetail($id);
        if (!$oldData)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Sản phẩm không tồn tại'], 404);

        // Thu thập thông tin sản phẩm chính
        $productData = [
            'category_id' => $_POST['category_id'] ?? $oldData['category_id'],
            'name' => $_POST['name'] ?? $oldData['name'],
            'gender' => $_POST['gender'] ?? $oldData['gender'],
            'description' => $_POST['description'] ?? $oldData['description'],
            'status' => $_POST['status'] ?? $oldData['status'],
            'is_featured' => isset($_POST['is_featured']) ? (int)$_POST['is_featured'] : $oldData['is_featured'],
        ];

        // Xử lý ảnh mới nếu có
        if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['primary_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $uploadDir = '../public/uploads/products/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0777, true);

                if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $uploadDir . $newName)) {
                    $primaryImageUrl = '/shop_giay_admin/public/uploads/products/' . $newName;
                    $productModel->setPrimaryImage($id, $primaryImageUrl);
                }
            }
        }

        $variants = [];

        if (isset($_POST['variants']['size'])) {
            $ids = $_POST['variants']['id'];
            $sizes = $_POST['variants']['size'];
            $colors = $_POST['variants']['color'];
            $skus = $_POST['variants']['sku'];
            $imports = $_POST['variants']['import_price'];
            $sales = $_POST['variants']['sale_price'];
            $stocks = $_POST['variants']['stock'];

            for ($i = 0; $i < count($sizes); $i++) {
                $variants[] = [
                    'id' => $ids[$i] ?: null,
                    'size' => $sizes[$i],
                    'color' => $colors[$i],
                    'import_price' => $imports[$i],
                    'sale_price' => $sales[$i],
                    'stock_quantity' => $stocks[$i],
                    'sku' => $skus[$i]
                ];
            }
        }

        if ($productModel->updateWithVariants($id, $productData, $variants)) {
            $auditModel->logAction($_SESSION['user_id'] ?? 1, 'UPDATE_PRODUCT_MULTI', 'products', $id, $oldData, ['product' => $productData, 'variants' => $variants]);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Cập nhật thành công']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi cập nhật'], 500);
    }

    // Xóa sản phẩm
    public function apiDelete($id = null)
    {
        AuthMiddleware::hasRole(['Admin']);

        if (!$id)
            return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);

        $productModel = $this->model('Product');

        // Check product status before deletion
        $product = $productModel->findById($id);
        if (!$product) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        if ($product['status'] !== 'Out of Stock') {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Chỉ có thể xóa sản phẩm khi trạng thái là "Out of Stock" (Hết hàng)'
            ], 400);
        }

        if ($productModel->delete($id)) {
            return $this->jsonResponse(['status' => 'success']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Lỗi khi xóa sản phẩm'], 500);
    }
    public function getVariants()
    {

        $product_id = $_GET['product_id'];

        $model = $this->model('ProductVariant');

        $data = $model->getByProduct($product_id);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $data
        ]);

    }
}
