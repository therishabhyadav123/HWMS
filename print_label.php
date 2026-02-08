<?php
include __DIR__ . "/db_connect.php";

if (!isset($_GET['report_id']) || !is_numeric($_GET['report_id'])) {
    die("<h2 style='color:red;text-align:center;'>❌ Invalid report_id!</h2>");
}

$report_id = intval($_GET['report_id']);

// Fetch report WITH USER NAME
$stmt = $conn->prepare("
    SELECT wr.*, 
           u.fullname AS user_fullname, 
           u.email AS user_email
    FROM waste_reports wr
    LEFT JOIN users u ON wr.user_id = u.id
    WHERE wr.id = ?
");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("<h2 style='color:red;text-align:center;'>❌ Report not found!</h2>");
}

$row = $res->fetch_assoc();

// ===========================
// QR TOKEN FIX — Auto-generate if missing
// ===========================
$qr_token = $row['qr_code'];

if (empty($qr_token)) {
    $qr_token = bin2hex(random_bytes(10));  
    $update = $conn->prepare("UPDATE waste_reports SET qr_code=? WHERE id=?");
    $update->bind_param("si", $qr_token, $report_id);
    $update->execute();
}

// ===========================
// QR IMAGE URL (Always Works)
// ===========================
$qr_payload = "http://localhost/hospitalwebsite/scan_handler.php?token=" . urlencode($qr_token);
$qr_img_url = "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=" . urlencode($qr_payload);

// ===========================
// USER NAME FIX (Correct Display)
// ===========================
$userDisplay = "Unknown User";

if (!empty($row['user_fullname'])) {
    $userDisplay = $row['user_fullname'];
} elseif (!empty($row['user_email'])) {
    $userDisplay = $row['user_email'];
}

// Date format
$created = date("d M, Y H:i", strtotime($row['created_at']));
?>
<!DOCTYPE html>
<html>
<head>
<title>Print Label</title>
<style>
body { font-family: Arial; background:#f4f4f4; padding:30px; }
.box {
    width:330px; margin:auto; background:white; padding:18px;
    border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.2);
}
.line { border-bottom:1px solid #ddd; margin:10px 0; }
.row { margin:6px 0; font-size:15px; }
.small { font-size:14px; }
.btn { width:100%; padding:10px; background:#0d7a63; 
       color:white; border:none; border-radius:6px; 
       margin-top:15px; cursor:pointer; }
@media print { .btn { display:none; } body { background:white; } }
</style>
</head>
<body>

<div class="box">
    <h2 style="text-align:center;color:#1e6de0;">Waste Label</h2>
    <div class="line"></div>

    <div class="row"><b>Report ID:</b> <?= $row['id'] ?></div>
    <div class="row"><b>Type:</b> <?= htmlspecialchars($row['waste_type']); ?></div>
    <div class="row"><b>Location:</b> <?= htmlspecialchars($row['location']); ?></div>
    <div class="row"><b>Quantity:</b> <?= htmlspecialchars($row['quantity']); ?></div>
    <div class="row"><b>Status:</b> <?= htmlspecialchars($row['status']); ?></div>
    <div class="row small"><b>User:</b> <?= htmlspecialchars($userDisplay); ?></div>
    <div class="row"><b>Date:</b> <?= $created ?></div>

    <div style="text-align:center; margin-top:12px;">
        <img src="<?= $qr_img_url ?>" width="180" height="180">
        <div style="font-size:13px;color:#555;">Scan for details</div>
    </div>

    <button class="btn" onclick="window.print()">Print</button>
</div>

</body>
</html>

