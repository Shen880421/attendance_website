<?php
header('Content-Type: application/json');
require_once '../inc/db.inc.php'; // 請根據實際路徑修改

// 取得學生姓名（從 GET 參數或 SESSION）
$student_name = isset($_GET['name']) ? $_GET['name'] : null;

if (!$student_name) {
    echo json_encode(['error' => '缺少學生名稱']);
    exit;
}

// 安全查詢資料庫
$sql = "SELECT 
            SUM(class_hours) AS class_hours,
            SUM(attended_hours) AS attended_hours,
            SUM(absent_hours) AS absent_hours,
            SUM(late_hours) AS late_hours,
            SUM(leave_early_hours) AS leave_early_hours
        FROM attendance_log
        WHERE student_name = :student_name";

$stmt = $pdo->prepare($sql);
$stmt->execute([':student_name' => $student_name]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);