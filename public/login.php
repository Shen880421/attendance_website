<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/twig.inc.php'; //載入twig 功能
require_once __DIR__ . '/../inc/db.inc.php'; //載入db 功能

// ... 接下來是 PHP 處理邏輯 ...
$message = "";
$alert_type = "";
if (isset($_GET['message']) &&  $_GET['message'] != "") {
    switch ($_GET['message']) {
        case 'nologin':
            $message .= "進入後台需登入";
            $alert_type = "alert-warning";
            break;
        default:
            $message .= "";
            $alert_type = "alert-success";
            break;
    }
}
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    //如果這個頁面的呼叫方式是使用http post 的話
    //var_dump($_POST);
    $acc = $_POST["account"];
    $pwd = $_POST["passwd"];
    // $role = $_POST['role'];

    if ($acc) {
        // 1. 查詢帳號對應的密碼雜湊值
        $stmt = $pdo->prepare("SELECT acc, pwd, role FROM admin_users WHERE acc = :acc");
        $stmt->execute([":acc" => $acc]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['backend_login_flag'] = true;
            $_SESSION['backend_login_acc'] = $acc;
            switch ($user['role']) {
                case 'admin':
                    header("location: admin_dashboard.php");
                    exit;
                case 'adv-user':
                    header("location: adv-user_dashboard.php");
                    exit;
                default:
                    header("location: normal-user_dashboard.php");
                    exit;
            }
        } else {
            $message = "登入失敗";
            $alert_type = "alert-danger";
        }
    }
}

echo $twig->render(
    'login.twig',
    [
        "title" => "出缺勤總覽",
        "message" => $message,
        "alert_type" => $alert_type
    ]
);
