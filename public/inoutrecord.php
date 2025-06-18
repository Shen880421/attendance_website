<?php
header('Content-Type: application/json');
require_once '../inc/db.inc.php'; // 請根據實際路徑修改

// 安全查詢資料庫
$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name']; // 從 fetch 傳入的 name
$sql = "SELECT 
            group_name,
            Name,
            `In/Out`,
            Time,
            Date,
            IPAddress
        FROM total_hours
        WHERE Name = :name
        ORDER BY
            Date";

$stmt = $pdo->prepare($sql);
$stmt->execute(['name' => 'Shen']);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);