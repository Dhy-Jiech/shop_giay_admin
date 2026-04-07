<?php
// app/Controllers/AdminController.php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AdminController extends Controller
{

    // Quản lý sản phẩm
    public function products()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales', 'Warehouse']);
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');

        $data['products'] = $productModel->getProductsWithDetails(50, 0);
        $data['categories'] = $categoryModel->findAll();

        $this->view('admin/products', $data);
    }

    public function orders()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');

        $params = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? 'All'
        ];

        $orders = $orderModel->searchAndFilter($params);
        $data = [
            'orders' => $orders,
            'filters' => $params
        ];
        $this->view('admin/orders', $data);
    }

    // Quản lý khách hàng
    public function customers()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $customerModel = $this->model('Customer');
        $tierModel = $this->model('CustomerTier');

        $params = [
            'search' => $_GET['search'] ?? '',
            'tier_id' => $_GET['tier_id'] ?? ''
        ];

        $customers = $customerModel->findAllWithStats($params);
        $tiers = $tierModel->findAll();

        $this->view('admin/customers', [
            'customers' => $customers,
            'tiers' => $tiers,
            'filters' => $params
        ]);
    }



    public function suppliers()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $supplierModel = $this->model('Supplier');
        $data['suppliers'] = $supplierModel->findAll();
        $this->view('admin/suppliers', $data);
    }

    public function categories()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $categoryModel = $this->model('Category');
        $data['categories'] = $categoryModel->findAll();
        $this->view('admin/categories', $data);
    }

    public function collections()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales', 'Warehouse']);
        $collectionModel = $this->model('Collection');
        $collections = $collectionModel->getAll();

        foreach ($collections as &$col) {
            $products = $collectionModel->getProducts($col['id']);
            $col['product_count'] = count($products);
        }

        $this->view('admin/collections', [
            'collections' => $collections
        ]);
    }

    public function auditLogs()
    {
        AuthMiddleware::hasRole(['Admin']);
        $auditModel = $this->model('AuditLog');
        $data['logs'] = $auditModel->getRecentLogs(50);
        $this->view('admin/audit_logs', $data);
    }

    public function webContent()
    {
        AuthMiddleware::hasRole(['Admin']);
        $contentModel = $this->model('WebContent');
        $data['banners'] = $contentModel->getActiveContents('Banner');
        $data['sliders'] = $contentModel->getActiveContents('Slider');

        $this->view('admin/web_content', $data);
    }

    public function dashboard()
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');

        $data = [
            'stats' => $orderModel->getRevenueByDateRange(
            date('Y-m-d', strtotime('-7 days')),
            date('Y-m-d')
        ),
            'revenue7days' => $orderModel->getRevenueLast7Days(),
            'topProducts' => $orderModel->getTopSellingProducts(),
            'orderStatus' => $orderModel->getOrderStatusStats(),
            'monthlyRevenue' => $orderModel->getMonthlyRevenue()
        ];

        $this->view('admin/dashboard', $data);
    }

    public function orderDetails($id)
    {
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $orderModel = $this->model('Order');
        $data['order'] = $orderModel->findById($id);
        $data['details'] = $orderModel->getDetails($id);

        // Render nội dung HTML cho AJAX gọi từ view orders.php
        echo "<div class='space-y-4 p-2'>";
        echo "<div class='grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl mb-4'>";
        echo "<div><p class='text-xs text-gray-500 uppercase font-bold'>Mã Đơn</p><p class='font-bold text-blue-600'>#{$data['order']['order_code']}</p></div>";
        echo "<div><p class='text-xs text-gray-500 uppercase font-bold'>Địa Chỉ Giao</p><p class='text-sm text-gray-700'>{$data['order']['shipping_address']}</p></div>";
        echo "</div>";
        echo "<table class='w-full text-sm'>";
        echo "<thead class='bg-gray-100 text-gray-600 border-b'><tr><th class='p-3 text-left'>Sản phẩm</th><th class='p-3 text-center'>Size / Màu</th><th class='p-3 text-center'>SL</th><th class='p-3 text-right'>Đơn giá</th></tr></thead>";
        echo "<tbody>";
        foreach ($data['details'] as $item) {
            $price = number_format($item['unit_price'], 0, ',', '.');
            echo "<tr class='border-b hover:bg-gray-50'><td class='p-3 font-medium text-gray-800'>{$item['product_name']}</td><td class='p-3 text-center text-gray-600'>{$item['size']} / {$item['color']}</td><td class='p-3 text-center font-bold'>{$item['quantity']}</td><td class='p-3 text-right text-red-600 font-semibold'>{$price}đ</td></tr>";
        }
        echo "</tbody></table>";
        $total = number_format($data['order']['final_amount'], 0, ',', '.');
        echo "<div class='flex justify-end mt-6'><div class='bg-red-50 px-6 py-3 rounded-2xl border border-red-100 items-center flex gap-4'><span class='text-gray-600 font-medium'>TỔNG THANH TOÁN:</span><span class='font-black text-2xl text-red-600'>{$total}đ</span></div></div>";
        echo "</div>";
    }
    public function inventory()
    {
        AuthMiddleware::hasRole(['Admin', 'Warehouse']);
        $receiptModel = $this->model('InventoryReceipt');
        $supplierModel = $this->model('Supplier');
        $productModel = $this->model('Product');

        $data['receipts'] = $receiptModel->getAll(); // Cần đảm bảo có method này
        $data['suppliers'] = $supplierModel->findAll();
        $data['products'] = $productModel->getProductsWithDetails(100, 0); // Lấy sp để chọn khi nhập kho

        $this->view('admin/inventory', $data);
    }
    public function promotions()
    {
        // Chuyển hướng đến PromotionController
        AuthMiddleware::hasRole(['Admin', 'Sales']);
        $promotionModel = $this->model('Promotion');
        $data['promotions'] = $promotionModel->findAll();
        $this->view('admin/promotions', $data);
    }
}
