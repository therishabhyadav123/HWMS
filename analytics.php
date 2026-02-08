<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Admin only */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* Language (optional – agar aap multilingual use kar rahe ho) */
$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

/* =======================
   BASIC COUNTS
======================= */
$totalReports = 0;
$pending = 0;
$assigned = 0;
$collected = 0;
$disposed = 0;

$res = $conn->query("
    SELECT status, COUNT(*) AS cnt 
    FROM waste_reports 
    GROUP BY status
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $totalReports += $row['cnt'];
        switch ($row['status']) {
            case 'Pending': $pending = $row['cnt']; break;
            case 'Assigned': $assigned = $row['cnt']; break;
            case 'Collected': $collected = $row['cnt']; break;
            case 'Disposed': $disposed = $row['cnt']; break;
        }
    }
}

/* =======================
   MONTHLY ANALYTICS (12 months)
======================= */
$months = [];
$monthCounts = [];

$q = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
    FROM waste_reports
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
    GROUP BY ym
    ORDER BY ym ASC
");

$map = [];
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $map[$r['ym']] = (int)$r['cnt'];
    }
}

/* build last 12 months */
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i month"));
    $months[] = date('M Y', strtotime($m . '-01'));
    $monthCounts[] = $map[$m] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $T['analytics'] ?? 'Analytics'; ?></title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">

<style>
body{font-family:"Manrope","Segoe UI",Arial,sans-serif;margin:0}
.container{max-width:1100px;margin:auto}
h2{color:#1e6de0}
.cards{display:flex;gap:16px;margin-bottom:20px}
.card{background:#fff;padding:20px;border-radius:10px;flex:1;
      box-shadow:0 4px 10px rgba(0,0,0,0.08)}
.card h3{margin:0;font-size:22px;color:#1e6de0}
.card p{margin:5px 0 0;color:#555}
.box{background:#fff;padding:20px;border-radius:10px;
     box-shadow:0 4px 10px rgba(0,0,0,0.08);margin-bottom:20px}
</style>
</head>

<body>
<?php $activePage = 'analytics'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="container">

<h2><?= $T['analytics'] ?? 'Analytics'; ?></h2>

<!-- SUMMARY CARDS -->
<div class="cards">
    <div class="card">
        <h3><?= $totalReports; ?></h3>
        <p><?= $T['total_reports'] ?? 'Total Reports'; ?></p>
    </div>
    <div class="card">
        <h3><?= $pending; ?></h3>
        <p><?= $T['pending'] ?? 'Pending'; ?></p>
    </div>
    <div class="card">
        <h3><?= $assigned; ?></h3>
        <p><?= $T['assigned'] ?? 'Assigned'; ?></p>
    </div>
    <div class="card">
        <h3><?= $collected; ?></h3>
        <p><?= $T['collected'] ?? 'Collected'; ?></p>
    </div>
    <div class="card">
        <h3><?= $disposed; ?></h3>
        <p><?= $T['disposed'] ?? 'Disposed'; ?></p>
    </div>
</div>

<!-- MONTHLY CHART -->
<div class="box">
    <h3><?= $T['monthly_reports'] ?? 'Monthly Reports (Last 12 Months)'; ?></h3>
    <canvas id="monthlyChart" height="120"></canvas>
</div>

</div>

<script>
const labels = <?= json_encode($months); ?>;
const data = <?= json_encode($monthCounts); ?>;

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Reports',
            data: data,
            borderColor: '#1e6de0',
            backgroundColor: 'rgba(13,99,87,0.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>

