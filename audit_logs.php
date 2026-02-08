<?php
session_start();
include __DIR__ . "/db_connect.php";

// Only admin allowed
if ($_SESSION['user']['role'] !== "admin") {
    die("Access denied");
}

$logs = $conn->query("
    SELECT a.*, u.fullname 
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Audit Logs</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">
<style>
body{font-family:"Manrope","Segoe UI",Arial,sans-serif;margin:0}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #ccc;padding:10px;}
</style>
</head>
<body>

<?php $activePage = 'audit_logs'; include __DIR__ . "/admin_layout_start.php"; ?>
<h2>Audit Logs</h2>

<table>
<tr>
    <th>User</th>
    <th>Action</th>
    <th>Meta</th>
    <th>IP</th>
    <th>Date</th>
</tr>

<?php while($l = $logs->fetch_assoc()): ?>
<tr>
    <td><?= $l['fullname'] ?></td>
    <td><?= $l['action'] ?></td>
    <td><?= $l['meta'] ?></td>
    <td><?= $l['ip_address'] ?></td>
    <td><?= $l['created_at'] ?></td>
</tr>
<?php endwhile; ?>

</table>

<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>
