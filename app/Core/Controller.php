<?php
// app/Core/Controller.php

class Controller
{
    protected function model($model)
    {
        require_once __DIR__ . "/../Models/" . $model . ".php";
        return new $model();
    }

    protected function view($view, $data = [])
    {
        if (file_exists(__DIR__ . "/../Views/" . $view . ".php")) {
            // Chuyển mảng thành các biến cho view
            extract($data);
            require_once __DIR__ . "/../Views/" . $view . ".php";
        }
        else {
            die("View $view does not exist.");
        }
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}
