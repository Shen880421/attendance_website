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
            class_date, class_hours, raw_hours  
        FROM attendance_log
        WHERE name = :name
        ORDER BY class_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['name' => $name]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);  // fetchAll，回傳多筆資料

echo json_encode($data, JSON_UNESCAPED_UNICODE);
