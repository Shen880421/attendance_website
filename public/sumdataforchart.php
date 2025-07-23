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
    // 查詢所有學生的統計資料
    $sql = "SELECT 
            FLOOR(COUNT(*) / 22) AS total_rows,
            COUNT(DISTINCT class_date) AS days,
            ROUND(SUM(class_hours) / 22, 2) AS class_hours,
            ROUND(SUM(attended_hours) / 22, 2) AS attended_hours,
            ROUND(SUM(absent_hours) / 22, 2) AS absent_hours,
            ROUND(SUM(late_hours) / 22, 2) AS late_hours,
            ROUND(SUM(leave_early_hours) / 22, 2) AS leave_early_hours,
            ROUND(SUM(raw_hours) / 22, 2) AS raw_hours
        FROM attendance_log";



    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    // 查詢特定學生的統計資料
    $sql = "SELECT 
                COUNT(*) AS total_rows,
                COUNT(DISTINCT class_date) AS days,
                SUM(class_hours) AS class_hours,
                SUM(attended_hours) AS attended_hours,
                SUM(absent_hours) AS absent_hours,
                SUM(late_hours) AS late_hours,
                SUM(leave_early_hours) AS leave_early_hours,
                SUM(raw_hours) AS raw_hours
            FROM attendance_log
            WHERE name = :name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name]);
}
$data = $stmt->fetch(PDO::FETCH_ASSOC); // ✅ 建議用 fetch() 回傳單筆彙總

echo json_encode($data, JSON_UNESCAPED_UNICODE);
