<?php
require_once __DIR__ . '/../inc/db.inc.php';
header('Content-Type: application/json');

// 接收參數
$name = $_GET['name'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if (!$name) {
  echo json_encode(['error' => 'Missing name']);
  exit;
}

try {
  // 🔹 attendance_log 區塊
  $params1 = [$name];
  $where1 = "name = ?";

  if ($startDate && $endDate) {
    $where1 .= " AND class_date BETWEEN ? AND ?";
    $params1[] = $startDate;
    $params1[] = $endDate;
  }

  $stmt1 = $pdo->prepare("
    SELECT * FROM attendance_log
    WHERE $where1
    ORDER BY class_date ASC
  ");
  $stmt1->execute($params1);
  $attendanceLog = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  // 🔹 total_hours 區塊（打卡紀錄）
  $params2 = [$name];
  $where2 = "Name = ?";

  if ($startDate && $endDate) {
    $where2 .= " AND Date BETWEEN ? AND ?";
    $params2[] = $startDate;
    $params2[] = $endDate;
  }

  $stmt2 = $pdo->prepare("
    SELECT * FROM total_hours
    WHERE $where2
    ORDER BY Date DESC, STR_TO_DATE(Time, '%l:%i%p') DESC
  ");
  $stmt2->execute($params2);
  $totalHours = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 回傳 JSON 結果
  echo json_encode([
    'attendance' => $attendanceLog,
    'records' => $totalHours
  ]);

} catch (PDOException $e) {
  echo json_encode([
    'error' => 'Database error',
    'message' => $e->getMessage()
  ]);
}
