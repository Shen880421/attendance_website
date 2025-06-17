<?php
header('Content-Type: application/json');
require_once '../inc/db.inc.php'; // 請根據實際路徑修改

// // 取得學生姓名（從 GET 參數或 SESSION）
// $student_name = isset($_GET['name']) ? $_GET['name'] : null;

// if (!$student_name) {
//     echo json_encode(['error' => '缺少學生名稱']);
//     exit;
// }

// 安全查詢資料庫
$sql = "WITH attendance AS (
        SELECT
            in_data.Name,
            in_data.group_name,
            in_data.real_date,
            TIMEDIFF(out_data.real_time, in_data.real_time) AS duration,
            ROUND(TIME_TO_SEC(TIMEDIFF(out_data.real_time, in_data.real_time)) / 3600, 2) AS duration_hours
        FROM
            (
                SELECT 
                    group_name,
                    Name,
                    `In/Out`,
                    STR_TO_DATE(Time, '%l:%i%p') AS real_time,
                    STR_TO_DATE(Date, '%Y-%m-%d') AS real_date
                FROM total_hours
                WHERE `In/Out` = 'in'
            ) AS in_data
        JOIN
            (
                SELECT 
                    group_name,
                    Name,
                    `In/Out`, 
                    STR_TO_DATE(Time, '%l:%i%p') AS real_time,
                    STR_TO_DATE(Date, '%Y-%m-%d') AS real_date
                FROM total_hours
                WHERE `In/Out` = 'out'
            ) AS out_data
        ON 
            in_data.Name = out_data.Name
            AND in_data.real_date = out_data.real_date
            AND in_data.group_name = out_data.group_name
        )

        SELECT
            a.Name,
            a.group_name,
            c.class_name,
            SUM(a.duration_hours) AS total_attendance_hours,
            SUM(c.class_hours) AS total_class_hours,
            LEAST(ROUND(SUM(a.duration_hours) / SUM(c.class_hours) * 100, 2), 100) AS completion_rate_pct
        FROM attendance a
        JOIN classes c
            ON a.group_name = c.group_name
            AND a.real_date = c.class_date
        WHERE a.name = :name  -- 這行限定查詢 Shen
        GROUP BY
            a.Name,
            a.group_name,
            c.class_name
        ORDER BY
            a.Name,
            c.class_name;";

$stmt = $pdo->prepare($sql);
$stmt->execute(['name' => 'Shen']);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
