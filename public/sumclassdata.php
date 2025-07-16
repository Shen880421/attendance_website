<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../inc/db.inc.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = isset($input['name']) ? $input['name'] : null;
if (!$name) {
    echo json_encode(['error' => '缺少學生名稱']);
    exit;
}

if ($name === 'all') {
    // 查詢所有學生的課程資料總和
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
                in_data.Name = out_data.Name COLLATE utf8mb4_unicode_ci
                AND in_data.real_date = out_data.real_date
                AND in_data.group_name = out_data.group_name COLLATE utf8mb4_unicode_ci
            )

            SELECT
                c.class_name,
                SUM(a.duration_hours) AS total_attendance_hours,
                SUM(c.class_hours) AS total_class_hours,
                LEAST(ROUND(SUM(a.duration_hours) / SUM(c.class_hours) * 100, 2), 100) AS completion_rate_pct
            FROM attendance a
            JOIN classes c
                ON a.group_name = c.group_name COLLATE utf8mb4_unicode_ci
                AND a.real_date = c.class_date
            GROUP BY
                c.class_name
            ORDER BY
                c.class_name;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    // 查詢特定學生的課程資料
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
                in_data.Name = out_data.Name COLLATE utf8mb4_unicode_ci
                AND in_data.real_date = out_data.real_date
                AND in_data.group_name = out_data.group_name COLLATE utf8mb4_unicode_ci
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
                ON a.group_name = c.group_name COLLATE utf8mb4_unicode_ci
                AND a.real_date = c.class_date
            WHERE a.name = :name COLLATE utf8mb4_unicode_ci
            GROUP BY
                a.Name,
                a.group_name,
                c.class_name
            ORDER BY
                a.Name,
                c.class_name;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name]);
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
