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
        $name = $_GET['name'] ?? ''; // 你需要知道是哪個學生的資料
        if (!$name) {
            die("缺少學生名稱");
        }


        // 查詢總筆數
        
        if (isset($_GET['page']) && $_GET['page'] != '') {
            $page = $_GET['page'];
        } else {
            $page = 0;
        }
        $rows_per_page = 10; //每頁幾筆資料
        $skip = $page * $rows_per_page; //跳過幾筆
        $stmt_count = $pdo->prepare("SELECT COUNT(*) AS cc FROM total_hours WHERE Name = :name");
        $stmt_count->execute(['name' => $name]);
        $result = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $total_pages = ceil($result['cc'] / $rows_per_page); //總頁數
        // $input = json_decode(file_get_contents('php://input'), true);
        // $name = $input['name']; // 從 fetch 傳入的 name
        $stmt = $pdo->prepare("SELECT group_name, Name, `In/Out`, Time, Date, IPAddress
                FROM total_hours WHERE Name = :name order by Date desc limit :skip, :rowsperpage");
        $stmt->bindParam(':skip', $skip, PDO::PARAM_INT);
        $stmt->bindParam(':rowsperpage', $rows_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        $data['prevpage'] = ($page - 1 > 0) ? $page - 1 : 0;
        $data['nextpage'] = $page + 1;
        $data["results"] = [];
        $rowCount = $stmt->rowCount();
        for ($i = 0; $i < $rowCount; $i++) {
            $row = $stmt->fetch();
            $data["results"][$i] = $row;
        }
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
