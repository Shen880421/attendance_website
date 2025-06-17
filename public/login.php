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

    if (filter_var($acc, FILTER_VALIDATE_EMAIL)) {
        //echo "合法 Email";
        $stmt = $pdo->prepare("select acc, pwd, role from admin_users where acc = :acc and pwd = :pwd");
        $result = $stmt->execute([
            ":acc" => $acc,
            ":pwd" => md5($pwd), //md5 加密使用者輸入的密碼後與資料表中的資料進行比對
        ]);

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            //var_dump($row);
            $_SESSION['backend_login_flag'] = true;
            $_SESSION['backend_login_acc'] = $acc;
            $_SESSION['backend_login_role'] = $row["role"];
            switch ($_SESSION['backend_login_role']) {
                case 'admin':
                    header("location: admin_dashboard.php");
                    exit;
                    break;

                case 'adv-user':
                    header("location: adv-user_dashboard.php");
                    exit;
                    break;

                default:
                    header("location: normal-user_dashboard.php");
                    exit;
                    break;
            }
        } else {
            $message = "登入失敗";
            $alert_type = "alert-danger";
        }
    } else {
        $message = "帳號電郵格式錯誤";
        $alert_type = "alert-warning";
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
