<?php
// app/Controllers/InventoryController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class InventoryController extends Controller
{
    public function index()
    {

        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $receiptModel = $this->model('InventoryReceipt');
        $supplierModel = $this->model('Supplier');
        $productModel = $this->model('Product');

        $receipts = $receiptModel->getAll();
        $suppliers = $supplierModel->getAll();
        $products = $productModel->getAll();

        $this->view('admin/inventory', [
            'receipts' => $receipts,
            'suppliers' => $suppliers,
            'products' => $products
        ]);

    }
    // API Nhập kho hàng loạt
    public function apiImport()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);

        $receiptModel = $this->model('InventoryReceipt');
        $detailModel = $this->model('InventoryReceiptDetail');
        $variantModel = $this->model('ProductVariant');

        $db = $receiptModel->getDb();

        try {

            $db->beginTransaction();

            $total_amount = 0;
            $items = $_POST['items'] ?? [];

            if (empty($items)) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Chưa thêm sản phẩm nhập'
                ]);
            }


            // 1️⃣ Sinh mã phiếu
            $receipt_code = $receiptModel->generateCode();


            // 2️⃣ Tạo phiếu nhập
            $receiptId = $receiptModel->insert([
                'receipt_code' => $receipt_code,
                'supplier_id' => $_POST['supplier_id'],
                'user_id' => $_SESSION['user_id'] ?? 1,
                'note' => $_POST['note'] ?? ''
            ]);


            // 3️⃣ Lưu chi tiết
            foreach ($items as $item) {

                $variant = $variantModel->findVariant(
                    $item['product_id'],
                    $item['size'],
                    $item['color']
                );

                if (!$variant) {
                    throw new Exception("Không tìm thấy biến thể sản phẩm");
                }

                $variant_id = $variant['id'];

                $item_total = $item['quantity'] * $item['import_price'];
                $total_amount += $item_total;

                $detailModel->insert([
                    'receipt_id' => $receiptId,
                    'product_variant_id' => $variant_id,
                    'quantity' => $item['quantity'],
                    'import_price' => $item['import_price'],
                    'total_price' => $item_total
                ]);

            }


            // 4️⃣ Update tổng tiền
            $receiptModel->update($receiptId, [
                'total_amount' => $total_amount
            ]);


            $db->commit();

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Nhập kho thành công',
                'receipt_code' => $receipt_code
            ]);

        }
        catch (Exception $e) {

            $db->rollBack();

            return $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);

        }

    }
    public function apiDelete()
    {

        $id = $_GET['id'];

        $model = $this->model('InventoryReceipt');

        $model->delete($id);

        return $this->jsonResponse([
            'status' => 'success',
            'message' => 'Đã xóa phiếu'
        ]);
    }

    public function getReceipt()
    {

        $id = $_GET['id'];

        $model = $this->model('InventoryReceiptDetail');

        $db = $model->getDb();

        $stmt = $db->prepare("
        SELECT 
        p.name as product_name,
        v.size,
        v.color,
        d.quantity,
        d.import_price
        FROM inventory_receipt_details d
        JOIN product_variants v 
        ON d.product_variant_id=v.id
        JOIN products p
        ON v.product_id=p.id
        WHERE d.receipt_id=?
        ");

        $stmt->execute([$id]);

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->jsonResponse([
            'items' => $items
        ]);

    }
}
