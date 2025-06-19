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
        if (!$name || !$date) {
            die("缺少學生名稱或日期");
        }
        // 查詢資料（不分頁，查出從該日期之後所有資料）
        $stmt = $pdo->prepare("
            SELECT group_name, Name, `In/Out`, Time, Date, IPAddress
            FROM total_hours
            WHERE Name = :name AND DATE(Date) >= :date
            ORDER BY Date DESC, Time DESC
            LIMIT 0, 10;
        ");
        $stmt->execute([
            ':name' => $name,
            ':date' => $date,
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['results'] = $results;  // 你剛剛的查詢結果
        $data['student'] = $name;
        $data['date'] = $date;
        $tmplFile = "/dashboard/inoutlist.twig";
        break;
    case 'insertdata':
        $name = $_GET['name'] ?? '';

        if (!$name) {
            die("缺少學生名稱");
        }
        // 查詢資料（不分頁，查出從該日期之後所有資料）
        $stmt = $pdo->prepare("
            SELECT Name, COUNT(*) 
            FROM total_hours
            WHERE Name = :name
            GROUP BY Name;
        ");
        $stmt->execute([
            ':name' => $name,

        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['results'] = $results;  // 你剛剛的查詢結果
        $data['student'] = $name;


        $tmplFile = "/dashboard/insertdata.twig";
        break;
    case 'savedata':
    // 確認是否有收到 POST 資料
    if (!isset($_POST['name'], $_POST['inout'], $_POST['time'], $_POST['date'], $_POST['IP'])) {
        die("缺少必要欄位");
    }
    $name = trim($_POST['name']);
    $inout = trim($_POST['inout']);
    $time = trim($_POST['time']);
    $date = trim($_POST['date']);
    $ip = trim($_POST['IP']);

    // 驗證基本格式（可選）
    if (!in_array($inout, ['in', 'out'])) {
        die("打卡狀態只能是 in 或 out");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO total_hours (group_name, Name, `In/Out`, Time, Date, IPAddress)
            VALUES (:group_name, :name, :inout, :time, :date, :ip)
        ");
        $stmt->execute([
            ':group_name' => 'FS101',
            ':name' => $name,
            ':inout' => $inout,
            ':time' => $time,
            ':date' => $date,
            ':ip' => $ip
        ]);



    } catch (PDOException $e) {
        die("資料新增失敗: " . $e->getMessage());
    }
        $data["message"] = "你新增了 " . $name . " 的 " . $stmt->rowCount() . " 筆資料。稍後自動跳轉<br>";
        $data["alert_type"] = "alert-success";
        $data["name"] = $name;
        $data["date"] = $date;
        $tmplFile = "/dashboard/message.twig";
        break;
    break;

    case 'deletedata':
        try {
            $stmt = $pdo->prepare("DELETE FROM total_hours 
            WHERE Name = :name AND `In/Out` = :inout AND Time = :time AND Date = :date");
            
            $stmt->execute([
                ":name"  => $_GET['name'],
                ":inout" => $_GET['inout'],
                ":time"  => $_GET['time'],
                ":date"  => $_GET['date']
            ]);

           
        } catch (PDOException $e) {
            die("資料刪除失敗: " . $e->getMessage());
        }
        $data["message"] = "你移除了 " . $_GET['name'] . " 的 " . $stmt->rowCount() . " 筆資料。稍後自動跳轉<br>";
        $data["alert_type"] = "alert-success";
        $data["name"] = $_GET['name'];
        $data["date"] = $_GET['date'];
        $tmplFile = "/dashboard/message.twig";
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
