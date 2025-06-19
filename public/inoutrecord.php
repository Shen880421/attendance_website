<?php
header('Content-Type: application/json');
require_once '../inc/db.inc.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$date = $input['date'] ?? '';

if (!$name) {
    echo json_encode([]);
    exit;
}

// 如果有指定日期就查 >=，否則查全部
if ($date) {
    $sql = "SELECT group_name, Name, `In/Out`, Time, Date, IPAddress
            FROM total_hours
            WHERE Name = :name AND Date >= :date
            ORDER BY Date, Time
            LIMIT 0, 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':date' => $date
    ]);
} else {
    $sql = "SELECT group_name, Name, `In/Out`, Time, Date, IPAddress
            FROM total_hours
            WHERE Name = :name
            ORDER BY Date, Time
            LIMIT 0, 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':name' => $name]);
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);