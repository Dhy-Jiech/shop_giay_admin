<?php
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class DashboardController extends Controller
{

    public function index()
    {
        $orderModel = $this->model('Order');

        $data = [
            'stats' => $orderModel->getRevenueByDateRange(
            date('Y-m-d', strtotime('-7 days')),
            date('Y-m-d')
        ) ?: [],

            'revenue7days' => $orderModel->getRevenueLast7Days(),
            'topProducts' => $orderModel->getTopSellingProducts(),
            'orderStatus' => $orderModel->getOrderStatusStats(),
            'monthlyRevenue' => $orderModel->getMonthlyRevenue()
        ];

        $this->view('admin/dashboard', $data);
    }
}
