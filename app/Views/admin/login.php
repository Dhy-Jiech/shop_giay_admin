<?php


$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống | Shop Giày</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #2a5298;
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background: #2a5298;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #1e3c72;
        }

        .error-message {
            background: #ffe0e0;
            color: #b30000;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            margin-top: 15px;
            color: #777;
        }

        @media (max-width: 420px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Đăng nhập hệ thống</h2>

    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="?url=login/authenticate">
        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">Đăng nhập</button>
    </form>

    <div class="footer-text">
        © <?= date('Y') ?> Shop Giày Admin Panel
    </div>
</div>

</body>
</html>