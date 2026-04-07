<!-- app/Views/admin/layouts/header.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?> - ĐỚ HA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .nav-item.active { background-color: #2563eb; color: white; }
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
            <h1 class="text-xl font-semibold text-gray-800"><?= $title ?? 'Tổng Quan' ?></h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Xin chào, <strong>Admin</strong></span>
                <img src="https://ui-avatars.com/api/?name=Admin" alt="Avatar" class="w-8 h-8 rounded-full">
            </div>
        </header>

        <!-- Content Area start -->
        <div class="p-6 flex-1 overflow-auto">
