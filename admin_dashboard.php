<?php
// admin_dashboard.php
session_start();
include __DIR__ . "/db_connect.php"; // expects $conn (mysqli)
$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

// Only allow admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/**
 * Helper: audit log
 */
function audit($conn, $user_id, $action, $meta = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $meta_json = $meta ? json_encode($meta) : null;
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, meta, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $meta_json, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Handle status update / assign / delete
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update status
    if (isset($_POST['update_status'])) {
        $id = intval($_POST['report_id']);
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE waste_reports SET status=?, updated_at=NOW() WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
        }
        audit($conn, $_SESSION['user']['id'] ?? $_SESSION['user']['user_id'], "update_status", ["report_id"=>$id, "status"=>$status]);
        header("Location: admin_dashboard.php");
        exit;
    }

    // assign collector
    if (isset($_POST['assign_collector'])) {
        $report_id = intval($_POST['assign_report_id']);
        $collector_id = intval($_POST['collector_id']);
        $u = $conn->prepare("UPDATE waste_reports SET assigned_collector_id = ?, status = 'Assigned', updated_at=NOW() WHERE id = ?");
        if ($u) {
            $u->bind_param("ii", $collector_id, $report_id);
            $u->execute();
            $u->close();
        }

        // optional: insert a message/notification for the collector
        $msgText = "You have been assigned to collect report #".$report_id;
        $m = $conn->prepare("INSERT INTO messages (from_user, to_user, report_id, message, channel) VALUES (?, ?, ?, ?, 'inapp')");
        $admin_id = $_SESSION['user']['id'] ?? $_SESSION['user']['user_id'];
        if ($m) {
            $m->bind_param("iiis", $admin_id, $collector_id, $report_id, $msgText);
            $m->execute();
            $m->close();
        }

        audit($conn, $admin_id, "assign_collector", ["report_id"=>$report_id, "collector_id"=>$collector_id]);
        header("Location: admin_dashboard.php");
        exit;
    }

    // delete report
    if (isset($_POST['delete_report'])) {
        $delete_id = intval($_POST['delete_id']);
        // optionally fetch row before delete for audit
        $res = $conn->prepare("SELECT id, qr_code FROM waste_reports WHERE id = ?");
        if ($res) {
            $res->bind_param("i", $delete_id);
            $res->execute();
            $r = $res->get_result()->fetch_assoc();
            $res->close();
        } else {
            $r = null;
        }

        $d = $conn->prepare("DELETE FROM waste_reports WHERE id = ?");
        if ($d) {
            $d->bind_param("i", $delete_id);
            $d->execute();
            $d->close();
        }

        audit($conn, $_SESSION['user']['id'] ?? null, "delete_report", ["report_id"=>$delete_id, "qr"=>$r['qr_code'] ?? null]);
        header("Location: admin_dashboard.php");
        exit;
    }
}

/**
 * Fetch stats for KPI cards
 */
$totalWaste = 0;
$pendingPickups = 0;
$highRiskItems = 0;

$res = $conn->query("SELECT COUNT(*) AS cnt FROM waste_reports");
if ($res) { $totalWaste = $res->fetch_assoc()['cnt']; }

$res = $conn->query("SELECT COUNT(*) AS cnt FROM waste_reports WHERE status IN ('Pending','Assigned','In_Progress','In_Progress')"); // normalized case
if ($res) { $pendingPickups = $res->fetch_assoc()['cnt']; }

$res = $conn->query("SELECT COUNT(*) AS cnt FROM waste_reports WHERE urgency='high' OR status='High_Risk' OR status='high_risk'");
if ($res) { $highRiskItems = $res->fetch_assoc()['cnt']; }

/**
 * Fetch reports
 */
$reportsQuery = "
    SELECT wr.*, u.fullname, u.email, c.fullname AS collector_name
    FROM waste_reports wr
    JOIN users u ON wr.user_id = u.id
    LEFT JOIN users c ON wr.assigned_collector_id = c.id
    ORDER BY wr.created_at DESC
";
$reports = $conn->query($reportsQuery);

/**
 * Fetch notifications (messages) - latest 8
 */
$messages = $conn->query("SELECT m.*, u.fullname as from_name FROM messages m LEFT JOIN users u ON m.from_user = u.id ORDER BY m.created_at DESC LIMIT 8");

/**
 * Fetch collectors for assign dropdown
 */
$collectors = $conn->query("SELECT id, fullname FROM users WHERE role = 'collector' ORDER BY fullname");

/**
 * Analytics data - monthly counts (last 12 months)
 */
$analytics = [];
$months = [];
$analyticsRes = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
    FROM waste_reports
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
    GROUP BY ym
    ORDER BY ym ASC
");
$monthMap = [];
if ($analyticsRes) {
    while ($row = $analyticsRes->fetch_assoc()) {
        $monthMap[$row['ym']] = intval($row['cnt']);
    }
}
// build last 12 months labels and values
for ($i=11; $i>=0; $i--) {
    $m = date('Y-m', strtotime("-$i month"));
    $months[] = date('M Y', strtotime($m.'-01'));
    $analytics[] = $monthMap[$m] ?? 0;
}

/**
 * Collector performance - simple counts per collector (last 30 days)
 */
$collectorPerfLabels = [];
$collectorPerfData = [];
$perfRes = $conn->query("
    SELECT u.fullname, COUNT(wr.id) AS cnt
    FROM waste_reports wr
    JOIN users u ON wr.assigned_collector_id = u.id
    WHERE wr.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY u.id
    ORDER BY cnt DESC
    LIMIT 8
");
if ($perfRes) {
    while ($row = $perfRes->fetch_assoc()) {
        $collectorPerfLabels[] = $row['fullname'];
        $collectorPerfData[] = intval($row['cnt']);
    }
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Mediecho</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard">
  <aside class="sidebar">
    <div class="brand">
      <span class="logo"><i class="fa-solid fa-shield-heart"></i></span>
      MediEco
    </div>
    <nav class="nav">
      <a class="active" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> <?= $T['dashboard']; ?></a>
      <a href="reports.php"><i class="fa-solid fa-clipboard"></i> <?= $T['reports']; ?></a>
      <a href="collectors.php"><i class="fa-solid fa-truck"></i> <?= $T['collectors']; ?></a>
      <a href="assignments.php"><i class="fa-solid fa-clipboard-check"></i> <?= $T['assignments']; ?></a>
      <a href="analytics.php"><i class="fa-solid fa-chart-line"></i> <?= $T['analytics']; ?></a>
      <a href="admin_chat_hub.php"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></a>
      <a href="settings.php"><i class="fa-solid fa-gear"></i> <?= $T['settings']; ?></a>
      <button class="js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"><i class="fa-solid fa-moon"></i> <?= $T['dark_mode']; ?></button>
    </nav>

    <div class="spacer"></div>

    <div class="profile-card">
      <div class="name"><?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></div>
      <div class="role">Admin</div>
    </div>
    <button class="btn btn-danger" onclick="window.location.href='index.html'"><i class="fa-solid fa-right-from-bracket"></i> <?= $T['logout']; ?></button>
  </aside>

  <main class="content">
    <div class="page-header">
      <div class="page-title">
        <h1>Admin Overview</h1>
        <p>Monitor system-wide waste management activities.</p>
      </div>
      <div class="page-actions">
        <button class="btn btn-outline" onclick="window.location.href='export_csv.php'"><i class="fa-solid fa-file-csv"></i> <?= $T['export_csv']; ?></button>
        <button class="btn btn-outline" onclick="window.location.href='export_pdf.php'"><i class="fa-solid fa-file-pdf"></i> <?= $T['export_pdf']; ?></button>
        <button class="btn btn-outline js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"></button>
      </div>
    </div>

    <div class="grid grid-2" style="margin-top:18px;">
      <div class="card">
        <h3>Quick Actions</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
          <input id="admin_report_id" type="number" placeholder="<?= $T['enter_report_id']; ?>" style="max-width:180px;">
          <button class="btn btn-primary" onclick="openAdminCert()"><i class="fa-solid fa-file-signature"></i> <?= $T['open_certificate']; ?></button>
          <button class="btn btn-outline" onclick="openChatMenu()"><i class="fa-solid fa-comment-dots"></i> <?= $T['sms']; ?></button>
          <button class="btn btn-outline" onclick="window.location.href='audit_logs.php'"><i class="fa-solid fa-clipboard-list"></i> <?= $T['audit_logs']; ?></button>
          <button class="btn btn-outline" onclick="window.location.href='user_management.php'"><i class="fa-solid fa-users"></i> <?= $T['user_management']; ?></button>
        </div>
      </div>

      <div class="card">
        <h3>Live Tools</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
          <button class="btn btn-outline" onclick="window.location.href='collector_map.php'"><i class="fa-solid fa-map-location-dot"></i> <?= $T['map']; ?></button>
          <button class="btn btn-outline" onclick="openFeature('notifications')"><i class="fa-solid fa-bell"></i> <?= $T['notifications']; ?></button>
          <button class="btn btn-outline" onclick="openFeature('alerts')"><i class="fa-solid fa-triangle-exclamation"></i> <?= $T['alerts']; ?></button>
          <form method="post" action="set_language.php">
            <select name="lang" onchange="this.form.submit()" style="min-width:140px;">
              <option value="en" <?= $lang=='en'?'selected':'' ?>>English</option>
              <option value="hi" <?= $lang=='hi'?'selected':'' ?>>Hindi</option>
              <option value="hinglish" <?= $lang=='hinglish'?'selected':'' ?>>Hinglish</option>
            </select>
          </form>
        </div>
      </div>
    </div>

    <div class="grid grid-3" style="margin-top:18px;">
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa fa-trash"></i></div>
        <div>
          <p class="stat-value"><?php echo number_format($totalWaste); ?></p>
          <p class="stat-label"><?= $T['total_waste']; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa fa-clock"></i></div>
        <div>
          <p class="stat-value"><?php echo number_format($pendingPickups); ?></p>
          <p class="stat-label"><?= $T['pending']; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div>
          <p class="stat-value"><?php echo number_format($highRiskItems); ?></p>
          <p class="stat-label"><?= $T['high_risk']; ?></p>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top:22px;">
      <h3><?= $T['reports']; ?></h3>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:70px"><?= $T['id']; ?></th>
              <th><?= $T['user']; ?></th>
              <th><?= $T['email']; ?></th>
              <th><?= $T['type']; ?></th>
              <th><?= $T['qty']; ?></th>
              <th style="min-width:220px"><?= $T['description']; ?></th>
              <th><?= $T['status']; ?></th>
              <th style="width:320px"><?= $T['action']; ?></th>
              <th><?= $T['date']; ?></th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $reports->fetch_assoc()): 
                $status = strtolower($row['status']);
                $badgeClass = 'badge pending';
                if (strpos($status,'assign') !== false) $badgeClass = 'badge assigned';
                if (strpos($status,'collect') !== false) $badgeClass = 'badge collected';
                if (strpos($status,'dispose') !== false) $badgeClass = 'badge disposed';
                if ($status === 'high_risk' || $status === 'high-risk' || $status === 'high') $badgeClass = 'badge highrisk';
            ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['fullname']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['waste_type'] ?? $row['category'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($row['quantity']); ?></td>
              <td><?php echo htmlspecialchars($row['description'] ?? $row['notes'] ?? ''); ?></td>
              <td><span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
              <td>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                  <form method="POST" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <input type="hidden" name="assign_report_id" value="<?php echo $row['id']; ?>">
                    <select name="collector_id" required style="min-width:160px;">
                      <option value=""><?= $T['assign_collector']; ?></option>
                      <?php
                          if ($collectors) {
                              $collectors->data_seek(0);
                              while($c = $collectors->fetch_assoc()){
                                  echo '<option value="'.intval($c['id']).'">'.htmlspecialchars($c['fullname']).'</option>';
                              }
                          }
                      ?>
                    </select>
                    <button type="submit" name="assign_collector" class="btn btn-primary"><?= $T['assign_collector']; ?></button>
                  </form>

                  <form method="POST" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                    <select name="status" required style="min-width:150px;">
                      <option value=""><?= $T['change']; ?></option>
                      <option value="Pending"><?= $T['pending']; ?></option>
                      <option value="Assigned"><?= $T['assigned']; ?></option>
                      <option value="In_Progress"><?= $T['in_progress']; ?></option>
                      <option value="Collected"><?= $T['collected']; ?></option>
                      <option value="Disposed"><?= $T['disposed']; ?></option>
                      <option value="High_Risk"><?= $T['high_risk']; ?></option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-outline"><?= $T['update']; ?></button>
                  </form>

                  <form method="POST" onsubmit="return confirm('<?= addslashes($T['confirm_delete']); ?>');">
                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_report" class="btn btn-danger"><?= $T['delete']; ?></button>
                  </form>

                  <button type="button" class="btn btn-outline" onclick="window.open('generate_certificate.php?id=<?php echo intval($row['id']); ?>','_blank')">
                    <i class="fa fa-file-pdf"></i> <?= $T['certificate']; ?>
                  </button>
                  <button type="button" class="btn btn-outline" onclick="window.open('print_label.php?report_id=<?php echo intval($row['id']); ?>','_blank')">
                    <i class="fa fa-print"></i> <?= $T['print']; ?>
                  </button>
                </div>
              </td>
              <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="grid grid-2" style="margin-top:22px;">
      <div class="card">
        <h3><?= $T['analytics']; ?> <span class="muted">(<?= $T['last_12_months']; ?>)</span></h3>
        <canvas id="analyticsChart" height="220"></canvas>
      </div>
      <div class="card">
        <h3><?= $T['collector_performance']; ?> <span class="muted">(<?= $T['last_30_days']; ?>)</span></h3>
        <canvas id="collectorChart" height="220"></canvas>
      </div>
    </div>
  </main>
</div>

<!-- TEMPLATES -->
<template id="tmpl-map" class="template">
  <div>
    <h3>Live Collector Map</h3>
    <p class="muted">Live locations of collectors (last known). Use this view to dispatch and monitor teams.</p>
    <div style="margin-top:12px;">
      <img src="/mnt/data/975237cc-932c-4507-94de-32ddf5243bf4.png" alt="Collector Map" style="width:100%;height:420px;object-fit:cover;border-radius:12px">
    </div>
  </div>
</template>

<template id="tmpl-notifications" class="template">
  <div>
    <h3>Notifications</h3>
    <div style="margin-top:10px;">
      <?php if ($messages && $messages->num_rows): ?>
        <?php
        $messages->data_seek(0);
        while($m = $messages->fetch_assoc()):
        ?>
          <div class="notice">
            <div style="font-weight:700"><?php echo htmlspecialchars($m['message']); ?></div>
            <div class="muted"><?php echo htmlspecialchars($m['created_at']); ?> · <?php echo htmlspecialchars($m['from_name'] ?? 'System'); ?></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div>No notifications</div>
      <?php endif; ?>
    </div>
  </div>
</template>

<template id="tmpl-alerts" class="template">
  <div>
    <h3>Emergency Alerts</h3>
    <p class="muted">Active high-risk alerts and emergency information.</p>
    <div style="margin-top:12px;">
      <?php
      $alertsRes = $conn->query("SELECT id, waste_type, location, status, created_at FROM waste_reports WHERE urgency='high' OR status LIKE '%high%' ORDER BY created_at DESC LIMIT 10");
      if ($alertsRes && $alertsRes->num_rows):
          while($a = $alertsRes->fetch_assoc()):
      ?>
        <div class="notice">
          <div style="font-weight:700">Report #<?php echo intval($a['id']); ?> — <?php echo htmlspecialchars($a['waste_type']); ?></div>
          <div class="muted"><?php echo htmlspecialchars($a['location']); ?> · <?php echo htmlspecialchars($a['status']); ?> · <?php echo date("d M Y, H:i", strtotime($a['created_at'])); ?></div>
        </div>
      <?php
          endwhile;
      else:
      ?>
        <div>No high-risk alerts</div>
      <?php endif; ?>
    </div>
  </div>
</template>

<!-- Modal backdrop -->
<div id="featureModalBackdrop" class="modal-backdrop" role="dialog" aria-hidden="true" style="display:none;">
  <div class="card" style="max-width:920px;width:100%;max-height:90vh;overflow:auto;position:relative;">
    <button class="btn btn-outline" onclick="closeFeature()" style="position:absolute;right:12px;top:12px;">Close</button>
    <div id="featureModalContent" style="margin-top:40px;"></div>
  </div>
</div>

<div id="chatModal" class="chat-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:1200;">
  <div class="card" style="width:min(360px,90%);margin:15% auto;text-align:center;">
    <h4>Select Chat</h4>
    <a href="chat.php?uid=USER_ID" class="btn btn-outline" style="display:block;margin:10px 0;">Chat with User</a>
    <a href="chat.php?uid=COLLECTOR_ID" class="btn btn-outline" style="display:block;margin:10px 0;">Chat with Collector</a>
    <button onclick="closeChatMenu()" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
  </div>
</div>

<script src="dashboard_theme.js"></script>
<script>
// prepare chart data from PHP
const analyticsLabels = <?php echo json_encode($months); ?>;
const analyticsData = <?php echo json_encode($analytics); ?>;
const collectorLabels = <?php echo json_encode($collectorPerfLabels); ?>;
const collectorData = <?php echo json_encode($collectorPerfData); ?>;

const ctx = document.getElementById('analyticsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: analyticsLabels,
        datasets: [{
            label: 'Reports',
            data: analyticsData,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            fill: true,
            tension: 0.3,
            pointRadius: 3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero:true } }
    }
});

const ctx2 = document.getElementById('collectorChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: collectorLabels,
        datasets: [{
            label: 'Completed pick-ups',
            data: collectorData,
            backgroundColor: '#1e6de0'
        }]
    },
    options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
});

function openAdminCert(){
  const id = document.getElementById('admin_report_id').value.trim();
  if(!id || isNaN(id) || Number(id) <= 0){
    alert('Please enter a valid report id');
    return;
  }
  window.open('generate_certificate.php?id=' + encodeURIComponent(id), '_blank');
}

const backdrop = document.getElementById('featureModalBackdrop');
const content = document.getElementById('featureModalContent');

function openFeature(name){
  const tmpl = document.getElementById('tmpl-' + name);
  if(!tmpl){
    alert('Feature not available: ' + name);
    return;
  }
  content.innerHTML = tmpl.innerHTML;
  backdrop.style.display = 'flex';
  backdrop.setAttribute('aria-hidden','false');
  document.body.style.overflow = 'hidden';
}

function closeFeature(){
  content.innerHTML = '';
  backdrop.style.display = 'none';
  backdrop.setAttribute('aria-hidden','true');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e){
  if(e.key === 'Escape'){
    closeFeature();
  }
});

function openChatMenu(){
    window.location.href = 'admin_chat_hub.php';
}
function closeChatMenu(){
    document.getElementById('chatModal').style.display = 'none';
}
</script>

</body>
</html>
