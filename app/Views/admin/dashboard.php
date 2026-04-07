<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Lý Siêu Thị Giày</title>
    <!-- TailwindCSS for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="flex bg-gray-100 min-h-screen">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex">
        <div class="h-16 flex items-center justify-center font-bold text-xl border-b border-gray-800">
            ĐỚ HA
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="/shop_giay_admin/public/?url=admin/dashboard" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Dashboard</a>
            <a href="/shop_giay_admin/public/?url=admin/products" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Sản Phẩm</a>
            <a href="/shop_giay_admin/public/?url=admin/categories" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Danh Mục</a>
            <a href="/shop_giay_admin/public/?url=admin/collections" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Collections / BTS</a>
            <a href="/shop_giay_admin/public/?url=admin/suppliers" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Nhà Cung Cấp</a>
            <a href="/shop_giay_admin/public/?url=admin/inventory" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Nhập Kho (Inventory)</a>
            <a href="/shop_giay_admin/public/?url=admin/orders" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Đơn Hàng</a>
            <a href="/shop_giay_admin/public/?url=admin/customers" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Khách Hàng</a>
            <a href="/shop_giay_admin/public/?url=admin/promotions" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors">Khuyến Mãi</a>
            <a href="/shop_giay_admin/public/?url=admin/auditLogs" class="nav-item block px-4 py-2 hover:bg-gray-800 rounded-md transition-colors border-t border-gray-800 mt-4 text-gray-400">Nhật Ký Hệ Thống</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="h-16 bg-white shadow-sm flex items-center px-6 justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Tổng Quan (Dashboard)</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Xin chào, <strong>Admin</strong></span>
                <img src="https://ui-avatars.com/api/?name=Admin" alt="Avatar" class="w-8 h-8 rounded-full">
            </div>
        </header>

        <!-- Content -->
        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Doanh thu 7 ngày qua</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">
                            <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?> VNĐ
                        </h3>
                    </div>
                    <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tổng Đơn Hàng</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">
                            <?= number_format($stats['total_orders'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Đơn Huỷ</p>
                        <h3 class="text-2xl font-bold text-red-600 mt-1">
                            <?= number_format($stats['cancelled_orders'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <div class="p-3 bg-red-100 text-red-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Charts -->
           <!-- Row 2: Revenue 7 days -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Doanh Thu 7 Ngày Gần Nhất
    </h3>
    <div class="h-80">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<!-- Row 3: 2 charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Top 5 Sản Phẩm Bán Chạy
        </h3>
        <div class="h-80">
            <canvas id="topProductChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Tỷ Lệ Trạng Thái Đơn Hàng
        </h3>
        <div class="h-80">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

</div>

<!-- Row 4 -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Doanh Thu Theo Tháng (Năm Nay)
    </h3>
    <div class="h-80">
        <canvas id="monthlyChart"></canvas>
    </div>
</div>
    </main>

    <script>
const revenueData = <?= json_encode($revenue7days) ?>;
const topProducts = <?= json_encode($topProducts) ?>;
const statusData = <?= json_encode($orderStatus) ?>;
const monthly = <?= json_encode($monthlyRevenue) ?>;

// ================= Revenue 7 Days =================
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueData.map(item => item.date),
        datasets: [{
            label: 'Doanh Thu (VNĐ)',
            data: revenueData.map(item => item.revenue),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// ================= Top Products =================
new Chart(document.getElementById('topProductChart'), {
    type: 'bar',
    data: {
        labels: topProducts.map(p => p.name),
        datasets: [{
            label: 'Số lượng bán',
            data: topProducts.map(p => p.total_sold),
            backgroundColor: '#6366f1',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// ================= Order Status =================
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(s => s.order_status),
        datasets: [{
            data: statusData.map(s => s.total),
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// ================= Monthly Revenue =================
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthly.map(m => 'Tháng ' + m.month),
        datasets: [{
            label: 'Doanh thu',
            data: monthly.map(m => m.revenue),
            backgroundColor: '#22c55e',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

    </script>
</body>
</html>
