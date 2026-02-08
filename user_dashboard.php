<?php
// user_dashboard.php
session_start();
include __DIR__ . "/db_connect.php"; // expects $conn (mysqli)

// Ensure logged-in user (role user)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: index.html");
    exit;
}

$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

$user_id = intval($_SESSION['user']['id']);
$userName = $_SESSION['user']['fullname'] ?? 'User';
$userEmail = $_SESSION['user']['email'] ?? '';
$firstLetter = strtoupper(substr($userName ?: $userEmail, 0, 1));

// Handle new waste report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_waste'])) {
    // fetch & sanitize
    $waste_type = trim($_POST['waste_type'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $urgency = in_array($_POST['urgency'] ?? 'normal', ['low','normal','high']) ? $_POST['urgency'] : 'normal';
    $location = trim($_POST['location'] ?? '');

    // photo upload (optional)
    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
            if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
            $fname = 'uploads/photo_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/' . $fname)) {
                $photo_path = $fname;
            }
        }
    }

    // insert report (prepared)
    $stmt = $conn->prepare("INSERT INTO waste_reports (user_id, waste_type, quantity, description, notes, urgency, status, location, photo, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())");
    if ($stmt) {
        $notes = ''; // kept for compatibility
        $stmt->bind_param("isssssss", $user_id, $waste_type, $quantity, $description, $notes, $urgency, $location, $photo_path);
        $stmt->execute();
        $report_id = $stmt->insert_id;
        $stmt->close();

        // generate secure qr token and update record
        $token = bin2hex(random_bytes(16));
        $upd = $conn->prepare("UPDATE waste_reports SET qr_code = ?, updated_at = NOW() WHERE id = ?");
        if ($upd) {
            $upd->bind_param("si", $token, $report_id);
            $upd->execute();
            $upd->close();
        }

        // insert an in-app notification for admin (optional)
        // find an admin to notify (first admin)
        $adminRes = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        if ($adminRes && $adminRes->num_rows) {
            $admin = $adminRes->fetch_assoc();
            $admin_id = intval($admin['id']);
            $msgText = "New waste report #$report_id submitted by {$userName}";
            $ins = $conn->prepare("INSERT INTO messages (`from_user`, `to_user`, report_id, message, channel) VALUES (?, ?, ?, ?, 'inapp')");
            if ($ins) {
                $ins->bind_param("iiis", $user_id, $admin_id, $report_id, $msgText);
                $ins->execute();
                $ins->close();
            }
        }

        // redirect to avoid resubmit
        header("Location: user_dashboard.php?ok=1");
        exit;
    } else {
        $errorMsg = "Database error: " . $conn->error;
    }
}

// fetch this user's reports (ordered newest first)
$reportsStmt = $conn->prepare("SELECT * FROM waste_reports WHERE user_id = ? ORDER BY created_at DESC");
$reportsStmt->bind_param("i", $user_id);
$reportsStmt->execute();
$reports = $reportsStmt->get_result();

// fetch notifications addressed to this user (latest 6)
$notifStmt = $conn->prepare("SELECT m.*, u.fullname as from_name FROM messages m LEFT JOIN users u ON m.from_user = u.id WHERE m.to_user = ? ORDER BY m.created_at DESC LIMIT 6");
$notifStmt->bind_param("i", $user_id);
$notifStmt->execute();
$notifications = $notifStmt->get_result();
$notifCount = $notifications ? $notifications->num_rows : 0;

// quick summary counts
$totalRes = $conn->prepare("SELECT COUNT(*) AS total FROM waste_reports WHERE user_id = ?");
$totalRes->bind_param("i", $user_id);
$totalRes->execute();
$total = $totalRes->get_result()->fetch_assoc()['total'] ?? 0;

$pendingRes = $conn->prepare("SELECT COUNT(*) AS pending FROM waste_reports WHERE user_id = ? AND status IN ('Pending','Assigned','In_Progress')");
$pendingRes->bind_param("i", $user_id);
$pendingRes->execute();
$pending = $pendingRes->get_result()->fetch_assoc()['pending'] ?? 0;

// Admin chat target
$adminChatId = 0;
$adminRes = $conn->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
if ($adminRes && $adminRes->num_rows) {
    $adminChatId = intval($adminRes->fetch_assoc()['id']);
}

?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $T['user_dashboard_title'] ?? 'User Dashboard'; ?> — Mediecho</title>
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
      <span class="logo"><i class="fa-solid fa-leaf"></i></span>
      MediEco
    </div>
    <nav class="nav">
      <a class="active" href="user_dashboard.php"><i class="fa-solid fa-grid-2"></i> <?= $T['overview'] ?? 'Overview'; ?></a>
      <a href="#reports"><i class="fa-solid fa-clipboard-list"></i> <?= $T['my_reports'] ?? 'My Reports'; ?></a>
      <a href="#notifications"><i class="fa-solid fa-bell"></i> <?= $T['notifications'] ?? 'Notifications'; ?></a>
      <a href="user_inbox.php"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></a>
      <button class="js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"><i class="fa-solid fa-moon"></i> <?= $T['theme'] ?? 'Theme'; ?></button>
    </nav>

    <div class="spacer"></div>

    <div class="profile-card">
      <div class="name"><?php echo htmlspecialchars($userName); ?></div>
      <div class="role"><?= $T['hospital_user'] ?? 'Hospital User'; ?></div>
    </div>
    <button class="btn btn-danger" onclick="window.location.href='index.html'"><i class="fa-solid fa-right-from-bracket"></i> <?= $T['logout'] ?? 'Log Out'; ?></button>
  </aside>

  <main class="content">
    <div class="page-header">
      <div class="page-title">
        <h1><?= $T['hospital_dashboard'] ?? 'Hospital Dashboard'; ?></h1>
        <p><?= $T['hospital_dashboard_subtitle'] ?? 'Manage your waste disposal requests and stay on top of updates.'; ?></p>
      </div>
      <div class="page-actions">
        <?php if ($adminChatId): ?>
          <button class="btn btn-outline" onclick="window.location.href='chat.php?uid=<?= $adminChatId; ?>'"><i class="fa-solid fa-comment-dots"></i> <?= $T['chat_admin'] ?? 'Chat Admin'; ?></button>
        <?php endif; ?>
        <button class="btn btn-outline" onclick="window.location.href='user_inbox.php'"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></button>
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
        <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($total); ?></p>
          <p class="stat-label"><?= $T['total_requests'] ?? 'Total Requests'; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($pending); ?></p>
          <p class="stat-label"><?= $T['pending_in_progress'] ?? 'Pending / In Progress'; ?></p>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon"><i class="fa-solid fa-bell"></i></div>
        <div>
          <p class="stat-value"><?php echo intval($notifCount); ?></p>
          <p class="stat-label"><?= $T['new_notifications'] ?? 'New Notifications'; ?></p>
        </div>
      </div>
    </div>

    <div class="grid grid-2" style="margin-top:22px;">
      <div class="card" id="new-report">
        <h3><?= $T['submit_waste_report'] ?? 'Submit Waste Report'; ?></h3>
        <?php if (!empty($errorMsg)): ?>
          <div class="notice" style="border-color:#fecaca;color:#b91c1c;background:#fee2e2;">
            <?php echo htmlspecialchars($errorMsg); ?>
          </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
          <div style="margin-bottom:12px;">
            <label><?= $T['category'] ?? 'Category'; ?></label>
            <select name="waste_type" required>
              <option value=""><?= $T['select_category'] ?? 'Select Category'; ?></option>
              <option value="General"><?= $T['waste_general'] ?? 'General'; ?></option>
              <option value="Infectious"><?= $T['waste_infectious'] ?? 'Infectious'; ?></option>
              <option value="Sharps"><?= $T['waste_sharps'] ?? 'Sharps'; ?></option>
              <option value="Pharmaceutical"><?= $T['waste_pharmaceutical'] ?? 'Pharmaceutical'; ?></option>
              <option value="Chemical"><?= $T['waste_chemical'] ?? 'Chemical'; ?></option>
            </select>
          </div>

          <div style="margin-bottom:12px;">
            <label><?= $T['urgency'] ?? 'Urgency'; ?></label>
            <select name="urgency">
              <option value="normal"><?= $T['urgency_normal'] ?? 'Normal'; ?></option>
              <option value="high"><?= $T['urgency_high'] ?? 'High'; ?></option>
              <option value="low"><?= $T['urgency_low'] ?? 'Low'; ?></option>
            </select>
          </div>

          <div style="margin-bottom:12px;">
            <label><?= $T['qty'] ?? 'Quantity'; ?></label>
            <input type="text" name="quantity" placeholder="<?= $T['quantity_placeholder'] ?? 'Quantity (e.g. 2 bags)'; ?>">
          </div>

          <div style="margin-bottom:12px;">
            <label><?= $T['description_notes'] ?? 'Description / Notes'; ?></label>
            <textarea name="description" rows="3" placeholder="<?= $T['description_placeholder'] ?? 'Add any additional details...'; ?>" required></textarea>
          </div>

          <div style="margin-bottom:12px;">
            <label><?= $T['location'] ?? 'Location'; ?></label>
            <input type="text" name="location" placeholder="<?= $T['location_placeholder'] ?? 'Ward / Room'; ?>">
          </div>

          <div style="margin-bottom:14px;">
            <label><?= $T['photo_optional'] ?? 'Photo (optional)'; ?></label>
            <input type="file" name="photo" accept="image/*">
          </div>

          <button class="btn btn-primary" type="submit" name="add_waste"><i class="fa fa-paper-plane"></i> <?= $T['submit_report'] ?? 'Submit Report'; ?></button>
        </form>
      </div>

      <div class="card soft" style="padding:0;overflow:hidden;">
        <img src="image.jpg" alt="Dashboard banner" style="width:100%;height:100%;object-fit:cover;min-height:320px;">
      </div>
    </div>

    <div class="grid grid-2" style="margin-top:22px;">
      <div class="card" id="notifications">
        <h3><?= $T['notifications'] ?? 'Notifications'; ?></h3>
        <?php if ($notifications && $notifications->num_rows): ?>
          <?php while($n = $notifications->fetch_assoc()): ?>
            <div class="notice">
              <div style="font-weight:700"><?php echo htmlspecialchars(substr($n['message'],0,100)); ?></div>
              <div class="muted"><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($n['created_at']))); ?> · <?php echo htmlspecialchars($n['from_name'] ?? 'System'); ?></div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="notice"><?= $T['no_notifications'] ?? 'No notifications'; ?></div>
        <?php endif; ?>
      </div>

      <div class="card soft">
        <h3><?= $T['quick_summary'] ?? 'Quick Summary'; ?></h3>
        <p class="muted"><?= $T['quick_summary_desc'] ?? 'Keep track of your latest submissions and pending work.'; ?></p>
        <div style="display:grid;gap:10px;margin-top:16px;">
          <div class="notice">
            <strong><?= $T['total_reports'] ?? 'Total Reports'; ?>:</strong> <?php echo intval($total); ?>
          </div>
          <div class="notice">
            <strong><?= $T['pending_in_progress'] ?? 'Pending / In-progress'; ?>:</strong> <?php echo intval($pending); ?>
          </div>
          <div class="notice">
            <strong><?= $T['average_response'] ?? 'Average Response'; ?>:</strong> <?= $T['average_response_value'] ?? '2-4 hours'; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card" id="reports" style="margin-top:22px;">
      <h3><?= $T['my_reports'] ?? 'My Reports'; ?></h3>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:60px"><?= $T['id'] ?? 'ID'; ?></th>
              <th><?= $T['category'] ?? 'Category'; ?></th>
              <th><?= $T['qty'] ?? 'Quantity'; ?></th>
              <th><?= $T['location'] ?? 'Location'; ?></th>
              <th><?= $T['status'] ?? 'Status'; ?></th>
              <th style="width:90px">QR</th>
              <th style="width:170px"><?= $T['action'] ?? 'Action'; ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $local_id = 1;
            if ($reports && $reports->num_rows):
              while($r = $reports->fetch_assoc()):
                $status = strtolower($r['status']);
                $badge = 'badge pending';
                if (strpos($status,'collect') !== false) $badge = 'badge collected';
                if (strpos($status,'high') !== false) $badge = 'badge highrisk';
                $qr_token = $r['qr_code'] ?? '';
                $qr_payload = htmlspecialchars("https://yourdomain.com/scan_handler.php?token=" . $qr_token);
                $qr_img = "https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl=".urlencode($qr_payload);
            ?>
            <tr>
              <td><?php echo $local_id; ?></td>
              <td><?php echo htmlspecialchars($r['waste_type'] ?? $r['category']); ?></td>
              <td><?php echo htmlspecialchars($r['quantity']); ?></td>
              <td><?php echo htmlspecialchars($r['location']); ?></td>
              <td>
                <?php if (strpos($status,'high') !== false): ?>
                  <span class="badge highrisk"><?= $T['high_risk'] ?? 'High Risk'; ?></span>
                <?php elseif (strpos($status,'collect') !== false): ?>
                  <span class="badge collected"><?= $T['collected'] ?? 'Collected'; ?></span>
                <?php else: ?>
                  <span class="badge pending"><?php echo htmlspecialchars($r['status']); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($qr_token)): ?>
                  <img src="<?php echo $qr_img; ?>" alt="QR" style="width:52px;height:52px;object-fit:cover;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($qr_token)): ?>
                  <button class="btn btn-outline" onclick="window.open('print_label.php?report_id=<?php echo intval($r['id']); ?>','_blank')"><i class="fa fa-print"></i> <?= $T['print'] ?? 'Print'; ?></button>
                  <button class="btn btn-outline" onclick="window.open('generate_certificate.php?report_id=<?php echo intval($r['id']); ?>','_blank')"><i class="fa fa-file-pdf"></i> <?= $T['pdf'] ?? 'PDF'; ?></button>
                <?php else: ?>
                  <button class="btn btn-outline" disabled>—</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php
              $local_id++;
              endwhile;
            else:
            ?>
            <tr><td colspan="7" style="padding:18px;text-align:center;"><?= $T['no_reports_yet'] ?? 'No reports yet'; ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="dashboard_theme.js"></script>
</body>
</html>
