<?php
$host = '172.17.0.2';
$port = 3306;
$dbName = 'attendance_web_project';
$username = 'root';
$password = 'ashen2250';
$charset = 'utf8mb4';           // 字元集，建議使用 utf8mb4 支援更多字元，如 Emoji
// DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";

// PDO 連接選項(有預設值, 如果沒有要改動預設值, 可以跳過不設定)

$options = [
    // PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 錯誤模式：拋出異常 (建議，有利於開發時發現問題)
    // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // 預設抓取模式：關聯陣列 (以欄位名稱作為鍵)
    // PDO::ATTR_EMULATE_PREPARES   => false,                 // 關閉模擬預處理 (PHP 8.3 預設為 false，提高安全性與效能)
];

try {
    $pdo = new PDO($dsn, $username, $password, $options); //$pdo 是資源變數
    //echo "資料庫連接成功！<br>";
} catch (PDOException $e) {
    // 連接失敗時捕獲異常並輸出錯誤訊息
    // 在實際專案中，不應該直接將詳細錯誤訊息顯示給使用者，應記錄到日誌檔，並給予使用者友善提示
    die("資料庫連接失敗: " . $e->getMessage());
    //echo "資料庫連接失敗: " . $e->getMessage();
}
