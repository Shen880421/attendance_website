<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db.inc.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';

if (!$name) {
    echo json_encode(['error' => 'Missing name']);
    exit;
}

if ($name === 'all') {
    // 查詢所有學生的每日統計資料
    $sql = "SELECT 
                class_date, 
                SUM(class_hours) as class_hours, 
                SUM(raw_hours) as raw_hours  
            FROM attendance_log
            GROUP BY class_date
            ORDER BY class_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    // 查詢特定學生的每日統計資料
    $sql = "SELECT 
                class_date, class_hours, raw_hours  
            FROM attendance_log
            WHERE name = :name
            ORDER BY class_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name]);
}
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);  // fetchAll，回傳多筆資料

echo json_encode($data, JSON_UNESCAPED_UNICODE);
