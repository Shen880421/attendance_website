<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/db.inc.php'; // 連接資料庫

$sql = "SELECT name FROM `attendance_log` GROUP BY name;";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($students);