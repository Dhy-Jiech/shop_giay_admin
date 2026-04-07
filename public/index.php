<?php
// public/index.php

session_start();

require_once '../app/Core/Database.php';
require_once '../app/Core/Model.php';
require_once '../app/Core/Controller.php';

// Simple Router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

$controllerName = isset($urlParts[0]) && !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'DashboardController';
$methodName = isset($urlParts[1]) ? $urlParts[1] : 'index';
$params = array_slice($urlParts, 2);

$controllerFile = '../app/Controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();

    if (method_exists($controller, $methodName)) {
        call_user_func_array([$controller, $methodName], $params);
    }
    else {
        http_response_code(404);
        echo "404 - Method not found";
    }
}
else {
    http_response_code(404);
    echo "404 - Controller not found: $controllerName";
}
