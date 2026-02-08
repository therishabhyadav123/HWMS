<?php
// generate_certificate.php
// Usage:
// - user:  generate_certificate.php?report_id=28
// - admin: generate_certificate.php?id=28
// Make sure db_connect.php path is correct.

include __DIR__ . '/db_connect.php'; // $conn (mysqli) expected

// Accept either id or report_id
$report_id = null;
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $report_id = intval($_GET['id']);
} elseif (isset($_GET['report_id']) && $_GET['report_id'] !== '') {
    $report_id = intval($_GET['report_id']);
}

if ($report_id === null || $report_id <= 0) {
    http_response_code(400);
    die("<h2 style='color:red; text-align:center;'>❌ Error: Report ID not provided in URL!</h2>");
}

// IMPORTANT: use the correct table name (your dashboard uses `waste_reports`)
$sql = "SELECT * FROM waste_reports WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    die("<h2 style='color:red; text-align:center;'>Database error: " . htmlspecialchars($conn->error) . "</h2>");
}
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    die("<h2 style='color:red; text-align:center;'>❌ Error: Report not found (ID: {$report_id})</h2>");
}
$row = $res->fetch_assoc();
$stmt->close();

// Map fields (adjust if your column names differ)
$waste_type  = $row['waste_type'] ?? 'N/A';
$user_id     = $row['user_id'] ?? '';
$quantity    = $row['quantity'] ?? '';
$location    = $row['location'] ?? '';
$status      = $row['status'] ?? 'Unknown';
$created_at  = $row['created_at'] ?? null;
$created_fmt = $created_at ? date("d M, Y", strtotime($created_at)) : date("d M, Y");

// Optionally fetch user name if you want nicer display
$user_name = '';
if (!empty($user_id)) {
    $uSt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ? LIMIT 1");
    if ($uSt) {
        $uSt->bind_param("i", $user_id);
        $uSt->execute();
        $uRes = $uSt->get_result();
        if ($uRes && $uRes->num_rows) {
            $uRow = $uRes->fetch_assoc();
            $user_name = $uRow['fullname'] ?: $uRow['email'];
        }
        $uSt->close();
    }
}
if (empty($user_name)) $user_name = 'Unknown User';

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Waste Disposal Certificate - Report #<?php echo htmlspecialchars($report_id); ?></title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f5f7f8;padding:30px}
.cert{max-width:760px;margin:20px auto;background:#fff;padding:28px;border-radius:10px;border:1px solid #e3e7ea;box-shadow:0 6px 24px rgba(20,35,42,0.06)}
h1{text-align:center;color:#0f2d34;margin-top:0}
.meta{margin-top:18px;font-size:16px;line-height:1.7}
.label{font-weight:700;color:#23363a}
.footer{margin-top:30px;text-align:center;color:#7b8790;font-size:14px}
.btn-print{display:inline-block;padding:8px 12px;background:#0f7b66;color:#fff;border-radius:8px;text-decoration:none;margin-top:12px}
</style>
</head>
<body>

<div class="cert">
  <h1>✔ Waste Disposal Certificate</h1>
  <p style="text-align:center;color:#34495e">This certifies that the following waste report has been recorded / processed.</p>

  <div class="meta">
    <p><span class="label">Report ID:</span> <?php echo htmlspecialchars($report_id); ?></p>
    <p><span class="label">Waste Type:</span> <?php echo htmlspecialchars($waste_type); ?></p>
    <p><span class="label">Quantity:</span> <?php echo htmlspecialchars($quantity); ?></p>
    <p><span class="label">Location:</span> <?php echo htmlspecialchars($location); ?></p>
    <p><span class="label">Submitted by (User):</span> <?php echo htmlspecialchars($user_name); ?></p>
    <p><span class="label">Status:</span> <?php echo htmlspecialchars($status); ?></p>
    <p><span class="label">Date:</span> <?php echo htmlspecialchars($created_fmt); ?></p>
  </div>

  <div style="text-align:center">
    <a class="btn-print" href="#" onclick="window.print();return false;">Print / Save as PDF</a>
  </div>

  <div class="footer">
    Hospital Waste Management System — Safe • Compliant • Auditable
  </div>
</div>

</body>
</html>
