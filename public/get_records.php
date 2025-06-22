<?php
require_once __DIR__ . '/../inc/db.inc.php';
header('Content-Type: application/json');

$name = $_GET['name'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if (!$name || !$startDate || !$endDate) {
  echo json_encode([]);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT * FROM total_hours 
    WHERE Name = ? AND Date BETWEEN ? AND ? 
    ORDER BY Date DESC, STR_TO_DATE(Time, '%l:%i%p') DESC
  ");
  $stmt->execute([$name, $startDate, $endDate]);
  $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($records);
} catch (PDOException $e) {
  echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
}
