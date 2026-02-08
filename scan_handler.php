<?php
session_start();
include __DIR__ . "/db_connect.php";

// Only collector allowed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== "collector") {
    die("Access denied.");
}

$token = $_GET['token'] ?? '';
if (!$token) {
    die("Invalid QR Code");
}

// Fetch report linked to QR token
$stmt = $conn->prepare("SELECT * FROM waste_reports WHERE qr_code = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    die("QR Not Found in Database.");
}

?>

<!DOCTYPE html>
<html>
<head>
<title>QR Scan Result</title>
<style>
body{font-family:Arial; background:#f4f7f9; padding:20px;}
.box{background:#fff;padding:20px;border-radius:10px;max-width:450px;margin:auto;box-shadow:0 4px 10px #ccc;}
button{padding:10px 20px;background:#1e6de0;color:#fff;border:none;border-radius:8px;margin-top:10px;}
</style>
</head>

<body>

<div class="box">
    <h2>QR Code Result</h2>
    <p><b>Report ID:</b> <?= $report['id'] ?></p>
    <p><b>Waste Type:</b> <?= $report['waste_type'] ?></p>
    <p><b>Status:</b> <?= $report['status'] ?></p>

    <form method="POST" action="update_status_from_qr.php">
        <input type="hidden" name="id" value="<?= $report['id'] ?>">
        <button type="submit">Mark as Collected</button>
    </form>
</div>

</body>
</html>

