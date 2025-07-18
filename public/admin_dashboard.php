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
            ':name' => $name
        ]);


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

    case 'editdata':
        // 顯示編輯表單
        $name = $_GET['name'] ?? '';
        $inout = $_GET['inout'] ?? '';
        $time = $_GET['time'] ?? '';
        $date = $_GET['date'] ?? '';

        if (!$name || !$inout || !$time || !$date) {
            die("缺少必要參數");
        }

        $data['edit_record'] = [
            'name' => $name,
            'inout' => $inout,
            'time' => $time,
            'date' => $date
        ];

        $tmplFile = "/dashboard/editdata.twig";
        break;

    case 'updatedata':
        // 處理更新資料
        if (!isset($_POST['original_name'], $_POST['original_inout'], $_POST['original_time'], $_POST['original_date'])) {
            die("缺少原始資料參數");
        }

        if (!isset($_POST['name'], $_POST['inout'], $_POST['time'], $_POST['date'])) {
            die("缺少更新資料參數");
        }

        $original_name = trim($_POST['original_name']);
        $original_inout = trim($_POST['original_inout']);
        $original_time = trim($_POST['original_time']);
        $original_date = trim($_POST['original_date']);

        $new_name = trim($_POST['name']);
        $new_inout = trim($_POST['inout']);
        $new_time = trim($_POST['time']);
        $new_date = trim($_POST['date']);
        $new_ip = trim($_POST['IP'] ?? '');

        // 驗證基本格式
        if (!in_array($new_inout, ['in', 'out'])) {
            die("打卡狀態只能是 in 或 out");
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE total_hours 
                SET Name = :new_name, `In/Out` = :new_inout, Time = :new_time, Date = :new_date, IPAddress = :new_ip
                WHERE Name = :original_name AND `In/Out` = :original_inout AND Time = :original_time AND Date = :original_date
            ");

            $stmt->execute([
                ':new_name' => $new_name,
                ':new_inout' => $new_inout,
                ':new_time' => $new_time,
                ':new_date' => $new_date,
                ':new_ip' => $new_ip,
                ':original_name' => $original_name,
                ':original_inout' => $original_inout,
                ':original_time' => $original_time,
                ':original_date' => $original_date
            ]);

            if ($stmt->rowCount() > 0) {
                $data["message"] = "你更新了 " . $original_name . " 的 " . $stmt->rowCount() . " 筆資料。稍後自動跳轉<br>";
                $data["alert_type"] = "alert-success";
            } else {
                $data["message"] = "沒有找到符合條件的資料進行更新";
                $data["alert_type"] = "alert-warning";
            }
        } catch (PDOException $e) {
            die("資料更新失敗: " . $e->getMessage());
        }

        $data["name"] = $new_name;
        $data["date"] = $new_date;
        $tmplFile = "/dashboard/message.twig";
        break;

    case 'adduser':
        $data = [];
        // 檢查是否為 POST 請求
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 驗證必填欄位
            if (empty($_POST['account']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['group_name'])) {
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
                            "INSERT INTO admin_users (acc, pwd, role, group_name) VALUES (:acc, :pwd, :role, :group_name)"
                        );
                        $stmt->execute([
                            ':acc' => $_POST['account'],
                            ':pwd' => $hashedPassword,
                            ':role' => $_POST['role'],
                            ':group_name' => $_POST['group_name']
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
    case 'createuser':
        $tmplFile = "dashboard/createuser.twig";
        break;
    case 'edituser':
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $uid = (int)$_GET['uid'];
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE no = :uid");
                $stmt->execute([':uid' => $uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $data['edit_user'] = $user;
                    $tmplFile = "dashboard/edituser.twig";
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
        } else {
            $data['message'] = '無效的使用者 ID';
            $data['alert_type'] = 'alert-danger';
            $data = array_merge($data, loaduser($pdo));
            $tmplFile = "dashboard/userlist.twig";
        }
        break;
    case 'saveedituser':
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $uid = (int)$_GET['uid'];
            $account = $_POST['account'] ?? '';
            $group_name = $_POST['group_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // 基本欄位驗證
            if (empty($account) || empty($role) || empty($group_name)) {
                $data['message'] = '帳號、班級代碼與身分別不可為空';
                $data['alert_type'] = 'alert-danger';
                // 重新載入該使用者資料
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE no = :uid");
                $stmt->execute([':uid' => $uid]);
                $data['edit_user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                $tmplFile = "dashboard/edituser.twig";
                break;
            }

            // 密碼驗證
            if (!empty($password) || !empty($password_confirm) || !empty('group_name')) {
                if ($password !== $password_confirm) {
                    $data['message'] = '兩次輸入的密碼不一致';
                    $data['alert_type'] = 'alert-danger';
                    // 重新載入該使用者資料
                    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE no = :uid");
                    $stmt->execute([':uid' => $uid]);
                    $data['edit_user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tmplFile = "dashboard/edituser.twig";
                    break;
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admin_users SET acc = :acc, role = :role, pwd = :pwd, group_name = :group_name WHERE no = :uid";
                $params = [
                    ':acc' => $account,
                    ':role' => $role,
                    ':pwd' => $hashed_password,
                    ':uid' => $uid,
                    ':group_name' => $group_name
                ];
            } else {
                $sql = "UPDATE admin_users SET acc = :acc, role = :role, group_name = :group_name WHERE no = :uid";
                $params = [
                    ':acc' => $account,
                    ':role' => $role,
                    ':uid' => $uid,
                    ':group_name' => $group_name
                ];
            }

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $data['message'] = '使用者資料已更新';
                $data['alert_type'] = 'alert-success';
                // 重新載入使用者列表
                $data = array_merge($data, loaduser($pdo));
                $tmplFile = "dashboard/userlist.twig";
            } catch (PDOException $e) {
                $data['message'] = '更新失敗：' . $e->getMessage();
                $data['alert_type'] = 'alert-danger';
                // 重新載入該使用者資料
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE no = :uid");
                $stmt->execute([':uid' => $uid]);
                $data['edit_user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                $tmplFile = "dashboard/edituser.twig";
            }
        }
        break;
    default:
        $tmplFile = "dashboard/admin.twig";
        break;
}


$data['useracc'] = $_SESSION['backend_login_acc'];

echo $twig->render($tmplFile, $data);
