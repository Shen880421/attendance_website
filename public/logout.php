<?php
session_start();
session_destroy(); //清空session 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登出出缺勤系統</title>
</head>

<body>
    帳號已登出<br>
    <div>等候跳轉或按<a href="login.php">此重新登入</a></div>
    <script>
        setTimeout(function() {
            window.location.href = "admin_dashboard.php?mode=inoutlist&name={{ name | url_encode }}&date=2025-04-15";
        }, 2000);
    </script>
</body>

</html>