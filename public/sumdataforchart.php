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
$data = $stmt->fetch(PDO::FETCH_ASSOC); // ✅ 建議用 fetch() 回傳單筆彙總

echo json_encode($data, JSON_UNESCAPED_UNICODE);