<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); //顯示錯誤訊息
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/twig.inc.php'; //載入twig 功能
require_once __DIR__ . '/../inc/db.inc.php'; //載入db 功能

//主要PHP程式區

//判斷後台是否有登入
if (isset($_SESSION['backend_login_flag']) && $_SESSION['backend_login_flag'] == true) {
    //如果有登入的話$_SESSION['backend_login_flag'] 為 true 
} else {
    header("location: login.php?message=nologin");
}
//用來判斷要用什麼版型渲染的變數
$mode = "";
if (isset($_GET['mode']) && $_GET['mode'] != "") {
    $mode = $_GET['mode'];
}
switch ($mode) {
    case 'inoutlist':
        $name = $_GET['name'] ?? '';
        $date = $_GET['date'] ?? ''; // 起始日期
        // var_dump($name);
        // var_dump($date);
        if (!$name || !$date) {
            die("缺少學生名稱或日期");
        }

        // 查詢資料（不分頁，查出從該日期之後所有資料）
        $stmt = $pdo->prepare("
            SELECT group_name, Name, `In/Out`, Time, Date, IPAddress
            FROM total_hours
            WHERE Name = :name AND DATE(Date) >= :date
            ORDER BY Date DESC, Time DESC
        ");
        $stmt->execute([
            ':name' => $name,
            ':date' => $date,
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // if (empty($results)) {
        //     echo "❌ 查無資料";
        //     var_dump($name);
        //     var_dump($date);
        // } else {
        //     echo "✅ 找到資料";
        //     var_dump($results);
        // }

        $data['results'] = $results;  // 你剛剛的查詢結果
        $data['student'] = $name;
        $data['date'] = $date;

        $tmplFile = "/dashboard/inoutlist.twig";
        break;

    case 'createuser':
        $tmplFile = "dashboard/createuser.twig";
        break;

    default:
        $tmplFile = "dashboard/admin.twig";
        break;
}


$data['useracc'] = $_SESSION['backend_login_acc'];

echo $twig->render($tmplFile, $data);
