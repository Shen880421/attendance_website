<?php
// 測試 Twig 模板，不需要資料庫連線
require_once '../vendor/autoload.php';  // 修正：加上 ../

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// 設定 Twig - 修正模板路徑
$loader = new FilesystemLoader(__DIR__ . '/../templates');  // 修正：加上 ../
$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

// 模擬資料，用來測試模板
$testData = [
    'message' => '這是測試訊息',
    'alert_type' => 'alert-info',  // Bootstrap 警告樣式
];

// 測試不同的訊息類型
if (isset($_GET['test'])) {
    switch ($_GET['test']) {
        case 'success':
            $testData['message'] = '登入成功！';
            $testData['alert_type'] = 'alert-success';
            break;
        case 'error':
            $testData['message'] = '帳號或密碼錯誤！';
            $testData['alert_type'] = 'alert-danger';
            break;
        case 'warning':
            $testData['message'] = '請注意：系統即將維護';
            $testData['alert_type'] = 'alert-warning';
            break;
        case 'empty':
            $testData = []; // 測試沒有訊息的情況
            break;
    }
}

try {
    // 渲染模板
    echo $twig->render('login.twig', $testData);
} catch (Exception $e) {
    echo "<h1>Twig 模板錯誤</h1>";
    echo "<div style='color: red; background: #ffe6e6; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<strong>錯誤訊息：</strong>" . $e->getMessage() . "<br>";
    echo "<strong>檔案：</strong>" . $e->getFile() . "<br>";
    echo "<strong>行數：</strong>" . $e->getLine();
    echo "</div>";
}
?>

<style>
    /* 開發工具樣式 */
    .dev-tools {
        position: fixed;
        top: 10px;
        right: 10px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        font-family: monospace;
        font-size: 12px;
        z-index: 9999;
    }

    .dev-tools a {
        display: block;
        margin: 2px 0;
        color: #007bff;
        text-decoration: none;
    }

    .dev-tools a:hover {
        text-decoration: underline;
    }
</style>

<!-- 開發測試工具 -->
<div class="dev-tools">
    <strong>🛠️ Twig 測試工具</strong><br>
    <a href="?">預設狀態</a>
    <a href="?test=success">成功訊息</a>
    <a href="?test=error">錯誤訊息</a>
    <a href="?test=warning">警告訊息</a>
    <a href="?test=empty">無訊息</a>
</div>