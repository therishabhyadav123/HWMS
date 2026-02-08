<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Admin only */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* Language (optional – multilingual support) */
$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

/* =========================
   ASSIGN COLLECTOR (POST)
========================= */
if (isset($_POST['assign'])) {
    $report_id    = intval($_POST['report_id']);
    $collector_id = intval($_POST['collector_id']);

    if ($report_id && $collector_id) {
        $stmt = $conn->prepare("
            UPDATE waste_reports
            SET assigned_collector_id = ?, status = 'Assigned', updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $collector_id, $report_id);
        $stmt->execute();
        $stmt->close();

        header("Location: assignment.php?success=1");
        exit;
    }
}

/* =========================
   FETCH UNASSIGNED REPORTS
========================= */
$reports = $conn->query("
    SELECT id, waste_type, status, created_at
    FROM waste_reports
    WHERE assigned_collector_id IS NULL
       OR status = 'Pending'
    ORDER BY created_at DESC
");

/* =========================
   FETCH ACTIVE COLLECTORS
========================= */
$collectors = $conn->query("
    SELECT id, fullname, email
    FROM users
    WHERE role = 'collector'
      AND status = 'active'
    ORDER BY fullname
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $T['assignments'] ?? 'Assignments'; ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">

<style>
body{font-family:"Manrope","Segoe UI",Arial,sans-serif;margin:0}
.container{max-width:1100px;margin:auto}
h2{color:#1e6de0}
table{width:100%;border-collapse:collapse;background:#fff}
th{background:#1e6de0;color:#fff;padding:10px;text-align:left}
td{padding:10px;border-bottom:1px solid #ddd}
select,button{padding:6px 10px}
button{background:#1e6de0;color:#fff;border:none;border-radius:6px;cursor:pointer}
.badge{padding:4px 10px;border-radius:20px;font-size:13px}
.pending{background:#fff3cd;color:#92400e}
.assigned{background:#e6ffef;color:#065f46}
.alert{background:#e6ffef;color:#065f46;padding:10px;border-radius:6px;margin-bottom:15px}
</style>
</head>

<body>
<?php $activePage = 'assignments'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="container">

<h2><?= $T['assignments'] ?? 'Assignments'; ?></h2>

<?php if (isset($_GET['success'])): ?>
<div class="alert">
    <?= $T['assigned_success'] ?? 'Collector assigned successfully'; ?>
</div>
<?php endif; ?>

<table>
<thead>
<tr>
    <th>ID</th>
    <th><?= $T['type'] ?? 'Waste Type'; ?></th>
    <th><?= $T['status'] ?? 'Status'; ?></th>
    <th><?= $T['date'] ?? 'Date'; ?></th>
    <th><?= $T['assign_collector'] ?? 'Assign Collector'; ?></th>
</tr>
</thead>

<tbody>
<?php if ($reports && $reports->num_rows > 0): ?>
    <?php while($r = $reports->fetch_assoc()): ?>
    <tr>
        <td><?= $r['id']; ?></td>
        <td><?= htmlspecialchars($r['waste_type']); ?></td>
        <td>
            <span class="badge <?= strtolower($r['status']); ?>">
                <?= htmlspecialchars($r['status']); ?>
            </span>
        </td>
        <td><?= date("d M Y", strtotime($r['created_at'])); ?></td>
        <td>
            <form method="POST" style="display:flex;gap:8px;">
                <input type="hidden" name="report_id" value="<?= $r['id']; ?>">

                <select name="collector_id" required>
                    <option value="">
                        <?= $T['select_collector'] ?? 'Select Collector'; ?>
                    </option>
                    <?php
                    if ($collectors) {
                        $collectors->data_seek(0);
                        while ($c = $collectors->fetch_assoc()) {
                            echo '<option value="'.$c['id'].'">'
                               . htmlspecialchars($c['fullname'])
                               . ' ('.$c['email'].')</option>';
                        }
                    }
                    ?>
                </select>

                <button type="submit" name="assign">
                    <?= $T['assign'] ?? 'Assign'; ?>
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5">
            <?= $T['no_pending_reports'] ?? 'No pending reports available'; ?>
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>

</div>
<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>

