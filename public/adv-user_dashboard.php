<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); //顯示錯誤訊息
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../inc/twig.inc.php'; //載入twig 功能
require_once __DIR__ . '/../inc/db.inc.php'; //載入db 功能

$students = [];
$stmt = $pdo->query("SELECT DISTINCT name FROM attendance_log ORDER BY name ASC");
while ($row = $stmt->fetch()) {
  $students[] = $row['name'];
}
$defaultStudent = $students[0] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>出勤儀表板</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }

    .card h6 {
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .card h5 {
      margin: 1rem 0 0.5rem;
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.25rem;
    }

    .form-label {
      font-weight: 600;
    }

    .table th {
      background-color: #f1f1f1;
    }

    canvas {
      max-height: 300px;
    }
  </style>
</head>

<body>
  <!-- 導覽列 -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">出缺勤系統（企業端）</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="切換導航">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="btn btn-danger" href="logout.php">登出</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 內容區塊 -->
  <div class="container mt-4">

    <!-- 學員選單 -->
    <div class="mb-4">
      <label for="studentSelect" class="form-label">選擇學員：</label>
      <select id="studentSelect" class="form-select">
        <?php foreach ($students as $student): ?>
          <option value="<?= htmlspecialchars($student) ?>" <?= $student == $defaultStudent ? 'selected' : '' ?>>
            <?= htmlspecialchars($student) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- 統計卡片 -->
    <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
      <div class="col">
        <div class="card shadow-sm p-4">
          <h6>總課程時數</h6>
          <div id="totalclasshours" class="fs-5 text-primary mb-3">---</div>
          <h6>實際上課時數</h6>
          <div id="attendance" class="fs-5 text-primary mb-3">---</div>
          <h6>出勤比率</h6>
          <div id="attendancerate" class="fs-5 text-primary">---</div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow-sm p-4">
          <h6>缺席時數</h6>
          <div id="unattendance" class="fs-5 text-primary mb-3">---</div>
          <h6>遲到時數</h6>
          <div id="late" class="fs-5 text-primary mb-3">---</div>
          <h6>早退時數</h6>
          <div id="leave_early" class="fs-5 text-primary">---</div>
        </div>
      </div>
    </div>

    <!-- 圖表 -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card shadow-sm p-3">
          <h5>每日上課時數折線圖</h5>
          <canvas id="myclasstimeChart"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm p-3">
          <h5>每日在校時數長條圖</h5>
          <canvas id="myattentimeChart"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm p-3">
          <h5>出/缺席圓餅圖</h5>
          <canvas id="myAttenChart"></canvas>
        </div>
      </div>
    </div>

    <!-- 打卡紀錄 -->
    <div class="mb-5">
      <h5 class="mb-3">打卡紀錄</h5>
      <form class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="startDate" class="form-label">起始日期</label>
          <input type="date" class="form-control" id="startDate">
        </div>
        <div class="col-md-4">
          <label for="endDate" class="form-label">結束日期</label>
          <input type="date" class="form-control" id="endDate">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="button" class="btn btn-primary w-100" onclick="filterRecords()">查詢</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>日期</th>
              <th>打卡時間</th>
              <th>狀態</th>
            </tr>
          </thead>
          <tbody id="recordTable">
            <!-- 動態生成紀錄 -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- 自訂邏輯放外部檔案或嵌入 -->
  <?php
  // 取得預設學生統計資料
  $stmt = $pdo->prepare("SELECT * FROM attendance_log WHERE name = ? ORDER BY class_date DESC");
  $stmt->execute([$defaultStudent]);
  $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 最新打卡記錄
  $stmt2 = $pdo->prepare("SELECT * FROM total_hours WHERE Name = ? ORDER BY Date DESC LIMIT 10");
  $stmt2->execute([$defaultStudent]);
  $records = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 可用資料塞入 JS 變數（chart, cards, records）
  ?>

  <script>
    const attendanceData = <?= json_encode($logs) ?>;
    const recordData = <?= json_encode($records) ?>;
    const currentStudent = <?= json_encode($defaultStudent) ?>;
  </script>

  <script>
    let classChart = null;
    let attendChart = null;
    let pieChart = null;


    document.addEventListener("DOMContentLoaded", () => {
      renderSummary(attendanceData);
      renderRecords(recordData);
    });

    function renderSummary(data) {
      if (!data.length) return;

      // 加總欄位
      const sum = (field) => data.reduce((a, b) => a + parseFloat(b[field] || 0), 0);

      const totalHours = sum("class_hours");
      const attendedHours = sum("attended_hours");
      const absentHours = sum("absent_hours");
      const lateHours = sum("late_hours");
      const leaveEarlyHours = sum("leave_early_hours");

      // 顯示加總結果
      document.getElementById("totalclasshours").textContent = totalHours.toFixed(1);
      document.getElementById("attendance").textContent = attendedHours.toFixed(1);
      document.getElementById("unattendance").textContent = absentHours.toFixed(1);
      document.getElementById("late").textContent = lateHours.toFixed(1);
      document.getElementById("leave_early").textContent = leaveEarlyHours.toFixed(1);

      // 出勤率：實際上課 ÷ 總課程
      let rate = 0;
      if (totalHours > 0) {
        rate = (attendedHours / totalHours) * 100;
      }
      document.getElementById("attendancerate").textContent = rate.toFixed(1) + "%";

      // 更新圖表
      drawCharts(data);
    }


    function drawCharts(data) {
      const labels = data.map(d => d.class_date);
      const classHours = data.map(d => parseFloat(d.class_hours));
      const rawHours = data.map(d => parseFloat(d.raw_hours));
      const present = data.map(d => parseFloat(d.attended_hours));
      const absent = data.map(d => parseFloat(d.absent_hours));

      // 銷毀舊圖表（如果存在）
      if (classChart) {
        classChart.destroy();
      }
      if (attendChart) {
        attendChart.destroy();
      }
      if (pieChart) {
        pieChart.destroy();
      }

      // 折線圖：每日上課時數
      const ctx1 = document.getElementById('myclasstimeChart').getContext('2d');
      classChart = new Chart(ctx1, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: '上課時數',
            data: classHours,
            borderColor: 'blue',
            backgroundColor: 'rgba(0,0,255,0.1)',
            tension: 0.3,
            fill: true
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });

      // 長條圖：在校時數
      const ctx2 = document.getElementById('myattentimeChart').getContext('2d');
      attendChart = new Chart(ctx2, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: '在校時數',
            data: rawHours,
            backgroundColor: 'rgba(0, 128, 0, 0.6)'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });

      // 圓餅圖：出席與缺席比
      const ctx3 = document.getElementById('myAttenChart').getContext('2d');
      const totalPresent = present.reduce((a, b) => a + b, 0);
      const totalAbsent = absent.reduce((a, b) => a + b, 0);

      pieChart = new Chart(ctx3, {
        type: 'pie',
        data: {
          labels: ['出席', '缺席'],
          datasets: [{
            data: [totalPresent, totalAbsent],
            backgroundColor: ['#4caf50', '#f44336']
          }]
        },
        options: {
          responsive: true
        }
      });
    }



    function renderRecords(records) {
      const tbody = document.getElementById("recordTable");
      tbody.innerHTML = "";

      records.forEach((r) => {
        const row = document.createElement("tr");
        row.innerHTML = `
      <td>${r.Date}</td>
      <td>${r.Time}</td>
      <td>${r["In/Out"]}</td>
    `;
        tbody.appendChild(row);
      });
    }

    document.getElementById("studentSelect").addEventListener("change", function () {
      const name = this.value;

      fetch(`get_dashboard_data.php?name=${encodeURIComponent(name)}`)
        .then(res => res.json())
        .then(data => {
          renderSummary(data.attendance);
          renderRecords(data.records);
        });
    });


    function loadDashboardData() {
      const student = document.getElementById("studentSelect").value;
      const startDate = document.getElementById("startDate").value;
      const endDate = document.getElementById("endDate").value;

      const params = new URLSearchParams({
        name: student,
        start_date: startDate,
        end_date: endDate
      });

      fetch(`get_dashboard_data.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
          renderSummary(data.attendance);
          renderRecords(data.records);
        });
    }

    function filterRecords() {
      const name = document.getElementById("studentSelect").value;
      const start = document.getElementById("startDate").value;
      const end = document.getElementById("endDate").value;

      if (!name || !start || !end) {
        alert("請選擇學員與完整的日期區間");
        return;
      }

      const params = new URLSearchParams({
        name: name,
        start_date: start,
        end_date: end
      });

      fetch(`get_records.php?${params}`)
        .then(res => res.json())
        .then(data => renderRecords(data));
    }

    function renderRecords(records) {
      const tbody = document.getElementById("recordTable");
      tbody.innerHTML = "";

      if (records.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center">查無資料</td></tr>`;
        return;
      }

      records.forEach((r) => {
        const row = document.createElement("tr");
        row.innerHTML = `
      <td>${r.Date}</td>
      <td>${r.Time}</td>
      <td>${r["In/Out"]}</td>
    `;
        tbody.appendChild(row);
      });
    }


    // 綁定查詢按鈕與下拉選單
    document.getElementById("studentSelect").addEventListener("change", loadDashboardData);
    document.querySelector("button.btn.btn-primary").addEventListener("click", loadDashboardData);
    document.addEventListener("DOMContentLoaded", loadDashboardData);

    // 預設載入
    document.addEventListener("DOMContentLoaded", loadDashboardData);
  </script>
</body>

</html>