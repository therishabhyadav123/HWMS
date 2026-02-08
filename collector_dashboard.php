<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Only collector allowed */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'collector') {
    header("Location: login.php");
    exit;
}

$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

$collector_id   = intval($_SESSION['user']['id']);
$collectorName  = $_SESSION['user']['fullname'];

/* =========================
   FETCH ASSIGNED REPORTS
========================= */
$stmt = $conn->prepare("
    SELECT wr.*, u.fullname AS user_name, u.email
    FROM waste_reports wr
    JOIN users u ON wr.user_id = u.id
    WHERE wr.assigned_collector_id = ?
    ORDER BY wr.created_at DESC
");
$stmt->bind_param("i", $collector_id);
$stmt->execute();
$reports = $stmt->get_result();

/* =========================
   KPI STATS
========================= */
$totalAssigned = $conn->query("
    SELECT COUNT(*) AS cnt 
    FROM waste_reports 
    WHERE assigned_collector_id = $collector_id
")->fetch_assoc()['cnt'];

$pending = $conn->query("
    SELECT COUNT(*) AS cnt 
    FROM waste_reports 
    WHERE assigned_collector_id = $collector_id 
      AND status IN ('Pending','Assigned','In_Progress')
")->fetch_assoc()['cnt'];

$completed = $conn->query("
    SELECT COUNT(*) AS cnt 
    FROM waste_reports 
    WHERE assigned_collector_id = $collector_id 
      AND status = 'Collected'
")->fetch_assoc()['cnt'];

// Admin chat target
$adminChatId = 0;
$adminRes = $conn->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
if ($adminRes && $adminRes->num_rows) {
    $adminChatId = intval($adminRes->fetch_assoc()['id']);
}

/* =========================
   NOTIFICATIONS
========================= */
$notifications = $conn->query("
    SELECT m.*, u.fullname AS sender
    FROM messages m
    LEFT JOIN users u ON m.from_user = u.id
    WHERE m.to_user = $collector_id
    ORDER BY m.created_at DESC
    LIMIT 8
");

/* =========================
   HANDLE STATUS UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $rid    = intval($_POST['report_id']);
    $status = $_POST['status'];

    $upd = $conn->prepare("
        UPDATE waste_reports 
        SET status = ?, updated_at = NOW() 
        WHERE id = ? AND assigned_collector_id = ?
    ");
    $upd->bind_param("sii", $status, $rid, $collector_id);
    $upd->execute();

    header("Location: collector_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $T['collector_dashboard_title'] ?? 'Collector Dashboard'; ?> | Mediecho</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">
</head>
<body>
<div class="dashboard">
  <aside class="sidebar">
    <div class="brand">
      <span class="logo"><i class="fa-solid fa-truck"></i></span>
      MediEco
    </div>
    <nav class="nav">
      <a class="active" href="collector_dashboard.php"><i class="fa-solid fa-truck-fast"></i> <?= $T['my_pickups'] ?? 'My Pickups'; ?></a>
      <a href="#assigned"><i class="fa-solid fa-clipboard-check"></i> <?= $T['assigned_jobs'] ?? 'Assigned Jobs'; ?></a>
      <a href="#notifications"><i class="fa-solid fa-bell"></i> <?= $T['notifications'] ?? 'Notifications'; ?></a>
      <a href="user_inbox.php"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></a>
      <a href="scan.html"><i class="fa-solid fa-qrcode"></i> <?= $T['scan_qr'] ?? 'Scan QR'; ?></a>
      <button class="js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"><i class="fa-solid fa-moon"></i> <?= $T['theme'] ?? 'Theme'; ?></button>
    </nav>

    <div class="spacer"></div>

    <div class="profile-card">
      <div class="name"><?php echo htmlspecialchars($collectorName); ?></div>
      <div class="role"><?= $T['collector_role'] ?? 'Collector'; ?></div>
    </div>
    <button class="btn btn-danger" onclick="window.location.href='index.html'"><i class="fa-solid fa-right-from-bracket"></i> <?= $T['logout'] ?? 'Log Out'; ?></button>
  </aside>

  <main class="content">
    <div class="page-header">
      <div class="page-title">
        <h1><?= $T['collector_dashboard_title'] ?? 'Collector Dashboard'; ?></h1>
        <p><?= $T['collector_dashboard_subtitle'] ?? 'View and manage your assigned pickups with live updates.'; ?></p>
      </div>
      <div class="page-actions">
        <?php if ($adminChatId): ?>
          <button class="btn btn-outline" onclick="location.href='chat.php?uid=<?= $adminChatId; ?>'"><i class="fa-solid fa-comment-dots"></i> <?= $T['chat_admin'] ?? 'Chat Admin'; ?></button>
        <?php endif; ?>
        <button class="btn btn-outline" onclick="location.href='user_inbox.php'"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></button>
        <button class="btn btn-outline" onclick="location.href='scan.html'"><i class="fa fa-qrcode"></i> <?= $T['scan_qr'] ?? 'Scan QR'; ?></button>
        <form method="post" action="set_language.php">
          <select name="lang" onchange="this.form.submit()" style="min-width:140px;">
            <option value="en" <?= $lang=='en'?'selected':'' ?>>English</option>
            <option value="hi" <?= $lang=='hi'?'selected':'' ?>>हिंदी</option>
            <option value="hinglish" <?= $lang=='hinglish'?'selected':'' ?>>Hinglish</option>
          </select>
        </form>
        <button class="btn btn-outline js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"></button>
      </div>
    </div>

    <div class="grid grid-3" style="margin-top:18px;">
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa-solid fa-list-check"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($totalAssigned); ?></p>
          <p class="stat-label"><?= $T['active_assignments'] ?? 'Active Assignments'; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($pending); ?></p>
          <p class="stat-label"><?= $T['pending_pickups'] ?? 'Pending Pickups'; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($completed); ?></p>
          <p class="stat-label"><?= $T['completed_jobs'] ?? 'Completed Jobs'; ?></p>
        </div>
      </div>
    </div>

    <div class="card" id="assigned" style="margin-top:22px;">
      <h3><?= $T['assigned_jobs'] ?? 'Assigned Jobs'; ?></h3>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th><?= $T['category'] ?? 'Category'; ?></th>
              <th><?= $T['location'] ?? 'Location'; ?></th>
              <th><?= $T['hospital'] ?? 'Hospital'; ?></th>
              <th><?= $T['status'] ?? 'Status'; ?></th>
              <th>QR</th>
              <th><?= $T['action'] ?? 'Action'; ?></th>
            </tr>
          </thead>
          <tbody>
          <?php
          $i=1;
          if ($reports->num_rows == 0):
          ?>
          <tr><td colspan="7" style="text-align:center;padding:20px;"><?= $T['no_assigned_reports'] ?? 'No assigned reports'; ?></td></tr>
          <?php endif; ?>

          <?php while($r = $reports->fetch_assoc()):
              $qr_payload = "http://localhost/hospitalwebsite/scan_handler.php?token=".$r['qr_code'];
              $qr_img = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=".urlencode($qr_payload);
          ?>
          <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($r['waste_type']); ?></td>
              <td><?php echo htmlspecialchars($r['location']); ?></td>
              <td><?php echo htmlspecialchars($r['user_name']); ?></td>
              <td>
                  <?php if ($r['status']=='Pending' || $r['status']=='Assigned'): ?>
                      <span class="badge pending"><?= $T['pending'] ?? 'Pending'; ?></span>
                  <?php elseif ($r['status']=='In_Progress'): ?>
                      <span class="badge progress"><?= $T['in_progress'] ?? 'In Progress'; ?></span>
                  <?php else: ?>
                      <span class="badge collected"><?= $T['collected'] ?? 'Collected'; ?></span>
                  <?php endif; ?>
              </td>
              <td>
                  <?php if(!empty($r['qr_code'])): ?>
                      <img src="<?php echo $qr_img; ?>" width="50" style="border-radius:10px;">
                  <?php else: ?> — <?php endif; ?>
              </td>
              <td>
                  <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                      <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                      <select name="status" style="min-width:160px;">
                          <option value="In_Progress"><?= $T['in_progress'] ?? 'In Progress'; ?></option>
                          <option value="Collected"><?= $T['collected'] ?? 'Collected'; ?></option>
                      </select>
                      <button type="submit" name="update_status" class="btn btn-primary"><?= $T['update'] ?? 'Update'; ?></button>
                  </form>
              </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card" id="notifications" style="margin-top:22px;">
      <h3><?= $T['notifications'] ?? 'Notifications'; ?></h3>
      <?php if ($notifications->num_rows==0): ?>
      <p class="muted"><?= $T['no_notifications'] ?? 'No notifications'; ?></p>
      <?php endif; ?>
      <?php while($n=$notifications->fetch_assoc()): ?>
      <div class="notice">
          <strong><?php echo htmlspecialchars($n['message']); ?></strong><br>
          <span class="muted"><?php echo $n['created_at']; ?> — <?php echo htmlspecialchars($n['sender'] ?? 'System'); ?></span>
      </div>
      <?php endwhile; ?>
    </div>

  </main>
</div>

<script src="collector_gps.js"></script>
<script src="dashboard_theme.js"></script>
<script>
function sendLocation(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(pos => {
            fetch("update_location.php", {
                method: "POST",
                headers: {"Content-Type":"application/x-www-form-urlencoded"},
                body: `lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`
            });
        });
    }
}
setInterval(sendLocation, 15000); // every 15 sec
sendLocation();
</script>
</body>
</html>
