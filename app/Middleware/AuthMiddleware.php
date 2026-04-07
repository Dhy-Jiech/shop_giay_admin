<?php
// app/Middleware/AuthMiddleware.php

class AuthMiddleware
{

    // Kiểm tra đăng nhập
    public static function checkLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /shop_giay_admin/public/?url=login");
            exit();
        }
    }

    // Kiểm tra quyền truy cập theo mảng các quyền cho phép
    public static function hasRole($allowed_roles = [])
    {
        self::checkLogin();

        // Mặc định role_id: 1 = Admin, 2 = Sales, 3 = Warehouse
        $current_role_id = $_SESSION['role_id'] ?? 0;

        $role_map = [
            1 => 'Admin',
            2 => 'Sales',
            3 => 'Warehouse'
        ];

        $current_role_name = $role_map[$current_role_id] ?? 'Guest';

        if (!in_array($current_role_name, $allowed_roles) && $current_role_name !== 'Admin') {
            http_response_code(403);
            die("Bạn không có quyền truy cập chức năng này.");
        }
    }
}
