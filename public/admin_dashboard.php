<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/twig.inc.php'; //載入twig 功能
require_once __DIR__ . '/../inc/db.inc.php'; //載入db 功能

// ... 接下來是 PHP 處理邏輯 ...

echo $twig->render(
    'admin.twig',
    [
        "title" => "出缺勤總覽",
        "message" => $message,
        "alert_type" => $alert_type
    ]
);