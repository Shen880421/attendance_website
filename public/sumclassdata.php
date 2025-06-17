<?php
require_once '../inc/db.inc.php';
header('Content-Type: application/json');


try {

    // 查詢 total_hours 表，計算每位學生的每日時數
    $sql_attendance = "
        SELECT Name, Date, Time, `In/Out`
        FROM total_hours
        WHERE group_name = 'FS101'
        ORDER BY Name, Date, Time
    ";
    $stmt = $pdo->query($sql_attendance);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 處理每日時數
    $attendance = [];
    $current_name = '';
    $current_date = '';
    $in_time = null;

    foreach ($attendance_records as $record) {
        $name = $record['Name'];
        $date = $record['Date'];
        $time = date('H:i', strtotime($record['Time']));
        $in_out = $record['In/Out'];

        if ($current_name !== $name || $current_date !== $date) {
            $in_time = null; // 重置 in_time 當切換學生或日期
        }

        if ($in_out === 'in') {
            $in_time = strtotime($time);
        } elseif ($in_out === 'out' && $in_time !== null) {
            $out_time = strtotime($time);
            if ($out_time > $in_time) {
                $hours = ($out_time - $in_time) / 3600; // 轉為小時
                if (!isset($attendance[$name])) {
                    $attendance[$name] = [];
                }
                $attendance[$name][$date] = $hours;
            }
            $in_time = null; // 重置 in_time 避免重複計算
        }

        $current_name = $name;
        $current_date = $date;
    }

    // 查詢 classes 表
    $sql_classes = "
        SELECT class_date, class_hours, class_name
        FROM classes
        WHERE group_name = 'FS101'
        ORDER BY class_date
    ";
    $stmt = $pdo->query($sql_classes);
    $classes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $date = date('Y/m/d', strtotime($row['class_date']));
        $classes[$date] = [
            'hours' => $row['class_hours'],
            'name' => $row['class_name']
        ];
    }

    // 計算達成率
    $results = [];
    foreach ($attendance as $name => $dates) {
        $results[$name] = [];
        foreach ($classes as $date => $class) {
            $class_name = $class['name'];
            $class_hours = $class['hours'];
            $attended_hours = isset($dates[$date]) ? min($dates[$date], $class_hours) : 0;
            if (!isset($results[$name][$class_name])) {
                $results[$name][$class_name] = ['total_attended' => 0, 'total_hours' => 0];
            }
            $results[$name][$class_name]['total_attended'] += $attended_hours;
            $results[$name][$class_name]['total_hours'] += $class_hours;
        }
        foreach ($results[$name] as $class_name => &$data) {
            $data['achievement_rate'] = $data['total_hours'] > 0 ? 
                ($data['total_attended'] / $data['total_hours']) * 100 : 0;
            $data['achievement_rate'] = number_format($data['achievement_rate'], 2);
        }
    }

    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => '資料庫錯誤: ' . $e->getMessage()]);
}
?>
