<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); //顯示錯誤訊息
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/twig.inc.php'; //載入twig 功能
require_once __DIR__ . '/../inc/db.inc.php'; //載入db 功能
//載入使用者清單
function loaduser($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM admin_users");
    $stmt->execute();
    $data['users'] = [];
    while ($row = $stmt->fetch()) {
        $data['users'][] = $row;
        // echo "讀取商品列表: " . $row['name'] . "<br>";
    }

    return $data;
}
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
    case 'adduser':
        $data = [];

        // 檢查是否為 POST 請求
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 驗證必填欄位
            if (empty($_POST['account']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
                $data['message'] = '請填寫所有必要欄位';
                $data['alert_type'] = 'alert-danger';
            } elseif ($_POST['password'] !== $_POST['confirm_password']) {
                $data['message'] = '密碼和確認密碼不一致';
                $data['alert_type'] = 'alert-danger';
            } else {
                try {
                    // 檢查帳號是否重複
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE acc = :acc");
                    $checkStmt->execute([':acc' => $_POST['account']]);

                    if ($checkStmt->fetchColumn() > 0) {
                        $data['message'] = '此帳號已存在';
                        $data['alert_type'] = 'alert-danger';
                    } else {
                        // 加密密碼
                        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

                        // 插入新使用者
                        $stmt = $pdo->prepare(
                            "INSERT INTO admin_users (acc, pwd, role) VALUES (:acc, :pwd, :role)"
                        );
                        $stmt->execute([
                            ':acc' => $_POST['account'],
                            ':pwd' => $hashedPassword,
                            ':role' => $_POST['role']
                        ]);

                        $data['message'] = '使用者新增成功！';
                        $data['alert_type'] = 'alert-success';
                    }
                } catch (PDOException $e) {
                    $data['message'] = '新增失敗：' . $e->getMessage();
                    $data['alert_type'] = 'alert-danger';
                }
            }
        }
        $data = loaduser($pdo);
        $tmplFile = "dashboard/userlist.twig";
        break;
    case 'createuser':
        $tmplFile = "dashboard/createuser.twig";
        break;
    case 'deleteuser':
        $data = [];

        // 檢查是否有用戶 ID
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $uid = (int)$_GET['uid'];

            // 檢查是否為 POST 確認刪除
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
                try {
                    // 先檢查用戶是否存在
                    $checkStmt = $pdo->prepare("SELECT acc, role FROM admin_users WHERE no = :uid");
                    $checkStmt->execute([':uid' => $uid]);
                    $user = $checkStmt->fetch();

                    if (!$user) {
                        $data['message'] = '找不到該使用者';
                        $data['alert_type'] = 'alert-danger';
                    } elseif ($user['role'] === 'admin' && $user['acc'] === $_SESSION['backend_login_acc']) {
                        // 防止刪除自己的管理員帳號
                        $data['message'] = '不能刪除自己的帳號';
                        $data['alert_type'] = 'alert-danger';
                    } else {
                        // 執行刪除
                        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE no = :uid");
                        $stmt->execute([':uid' => $uid]);

                        if ($stmt->rowCount() > 0) {
                            $data['message'] = "使用者 {$user['acc']} 刪除成功！";
                            $data['alert_type'] = 'alert-success';
                        } else {
                            $data['message'] = '刪除失敗，請重試';
                            $data['alert_type'] = 'alert-danger';
                        }
                    }
                } catch (PDOException $e) {
                    $data['message'] = '刪除失敗：' . $e->getMessage();
                    $data['alert_type'] = 'alert-danger';
                }

                // 刪除後重新導向到使用者列表
                $data = array_merge($data, loaduser($pdo));
                $tmplFile = "dashboard/userlist.twig";
            } else {
                // 顯示刪除確認頁面
                try {
                    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE no = :uid");
                    $stmt->execute([':uid' => $uid]);
                    $user = $stmt->fetch();

                    if ($user) {
                        $data['user_to_delete'] = $user;
                        $tmplFile = "dashboard/deleteuser.twig";
                    } else {
                        $data['message'] = '找不到該使用者';
                        $data['alert_type'] = 'alert-danger';
                        $data = array_merge($data, loaduser($pdo));
                        $tmplFile = "dashboard/userlist.twig";
                    }
                } catch (PDOException $e) {
                    $data['message'] = '查詢失敗：' . $e->getMessage();
                    $data['alert_type'] = 'alert-danger';
                    $data = array_merge($data, loaduser($pdo));
                    $tmplFile = "dashboard/userlist.twig";
                }
            }
        } else {
            $data['message'] = '無效的使用者 ID';
            $data['alert_type'] = 'alert-danger';
            $data = array_merge($data, loaduser($pdo));
            $tmplFile = "dashboard/userlist.twig";
        }
        break;
    case 'userlist':
        $data = loaduser($pdo);
        $tmplFile = "dashboard/userlist.twig";
        break;
    default:
        $tmplFile = "dashboard/admin.twig";
        break;
}


$data['useracc'] = $_SESSION['backend_login_acc'];

echo $twig->render($tmplFile, $data);
