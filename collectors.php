<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Only admin allowed */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* Language load (optional – agar aap multilingual use kar rahe ho) */
$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

/* Total collectors */
$totalCollectors = 0;
$activeCollectors = 0;
$inactiveCollectors = 0;

/* Count collectors */
$res = $conn->query("SELECT status, COUNT(*) AS cnt FROM users WHERE role='collector' GROUP BY status");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $totalCollectors += $row['cnt'];
        if ($row['status'] === 'active') $activeCollectors = $row['cnt'];
        if ($row['status'] === 'inactive') $inactiveCollectors = $row['cnt'];
    }
}

/* Fetch collector list */
$collectors = $conn->query("
    SELECT id, fullname, email, status, created_at
    FROM users
    WHERE role='collector'
    ORDER BY fullname
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $T['collectors'] ?? 'Collectors'; ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">

<style>
body{font-family:"Manrope","Segoe UI",Arial,sans-serif;margin:0}
.container{max-width:1100px;margin:auto}
h2{color:#1e6de0}
.stats{display:flex;gap:20px;margin-bottom:20px}
.card{background:#fff;padding:20px;border-radius:10px;flex:1;
      box-shadow:0 4px 10px rgba(0,0,0,0.08)}
.card h3{margin:0;font-size:22px;color:#1e6de0}
.card p{margin:5px 0 0;color:#555}

table{width:100%;border-collapse:collapse;background:#fff}
th{background:#1e6de0;color:#fff;padding:10px;text-align:left}
td{padding:10px;border-bottom:1px solid #ddd}
.badge{padding:5px 10px;border-radius:20px;font-size:13px}
.active{background:#e6ffef;color:#065f46}
.inactive{background:#ffecec;color:#9b1c1c}
</style>
</head>

<body>
<?php $activePage = 'collectors'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="container">

<h2><?= $T['collectors'] ?? 'Collectors'; ?></h2>

<!-- Stats Cards -->
<div class="stats">
    <div class="card">
        <h3><?= $totalCollectors; ?></h3>
        <p><?= $T['total_collectors'] ?? 'Total Collectors'; ?></p>
    </div>
    <div class="card">
        <h3><?= $activeCollectors; ?></h3>
        <p><?= $T['active_collectors'] ?? 'Active Collectors'; ?></p>
    </div>
    <div class="card">
        <h3><?= $inactiveCollectors; ?></h3>
        <p><?= $T['inactive_collectors'] ?? 'Inactive Collectors'; ?></p>
    </div>
</div>

<!-- Collectors Table -->
<table>
<thead>
<tr>
    <th>ID</th>
    <th><?= $T['user'] ?? 'Name'; ?></th>
    <th><?= $T['email'] ?? 'Email'; ?></th>
    <th><?= $T['status'] ?? 'Status'; ?></th>
    <th><?= $T['date'] ?? 'Joined On'; ?></th>
</tr>
</thead>
<tbody>
<?php if ($collectors && $collectors->num_rows > 0): ?>
    <?php while($c = $collectors->fetch_assoc()): ?>
    <tr>
        <td><?= $c['id']; ?></td>
        <td><?= htmlspecialchars($c['fullname']); ?></td>
        <td><?= htmlspecialchars($c['email']); ?></td>
        <td>
            <span class="badge <?= $c['status']=='active'?'active':'inactive'; ?>">
                <?= ucfirst($c['status']); ?>
            </span>
        </td>
        <td><?= date("d M Y", strtotime($c['created_at'])); ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="5">No collectors found</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>

