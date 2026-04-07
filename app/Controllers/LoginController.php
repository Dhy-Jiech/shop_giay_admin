<?php
require_once __DIR__ . '/../Models/User.php';

class LoginController
{
    private $userModel;

    public function __construct()
    {

        $this->userModel = new User();
    }

    // Hiển thị form login
    public function index()
    {
        require_once __DIR__ . '/../views/admin/login.php';
    }

    // Xử lý đăng nhập
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=login");
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin.";
            header("Location: ?url=login");
            exit();
        }

        // Lấy user từ database
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $_SESSION['error'] = "Tài khoản không tồn tại.";
            header("Location: ?url=login");
            exit();
        }

        // Kiểm tra trạng thái tài khoản
        if ((int)$user['is_active'] !== 1) {
            $_SESSION['error'] = "Tài khoản đã bị khóa.";
            header("Location: ?url=login");
            exit();
        }

        // Kiểm tra mật khẩu
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Sai mật khẩu.";
            header("Location: ?url=login");
            exit();
        }

        // Đăng nhập thành công → lưu session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];

        header("Location: ?url=dashboard");
        exit();
    }

    // Logout
    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: ?url=login");
        exit();
    }
}