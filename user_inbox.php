<?php
session_start();
include __DIR__ . "/db_connect.php";

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$my_id = intval($_SESSION['user']['id']);
$my_role = $_SESSION['user']['role'] ?? '';
$pageTitle = $my_role === 'admin' ? 'Admin Messages' : 'My Messages';
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($pageTitle); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">
<style>
.inbox{max-width:420px;margin:30px auto;background:#fff;border-radius:10px;padding:15px}
.item{padding:10px;border-bottom:1px solid #eee}
.item a{text-decoration:none;color:#000;font-weight:700}
.badge{float:right;background:red;color:#fff;padding:2px 7px;border-radius:50%}
.role{display:block;font-size:12px;color:#666;margin-top:4px}
</style>
</head>
<body>

<?php if ($my_role === 'admin'): ?>
  <?php $activePage = 'messages'; include __DIR__ . "/admin_layout_start.php"; ?>
<?php endif; ?>

<div class="inbox">
<h3><?= $my_role === 'admin' ? 'Admin Inbox' : 'My Inbox'; ?></h3>

<?php
$res = null;
if ($my_role === 'admin') {
  $q = $conn->prepare("
    SELECT u.id, u.fullname, u.role,
           SUM(CASE WHEN c.receiver_id = ? AND c.is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM users u
    LEFT JOIN chat c ON c.sender_id = u.id AND c.receiver_id = ?
    WHERE u.role IN ('user','collector')
    GROUP BY u.id, u.fullname, u.role
    ORDER BY u.role, u.fullname
  ");
  $q->bind_param("ii", $my_id, $my_id);
  $q->execute();
  $res = $q->get_result();
} else {
  $q = $conn->prepare("
    SELECT u.id, u.fullname, u.role,
           SUM(CASE WHEN c.receiver_id = ? AND c.is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM users u
    LEFT JOIN chat c ON c.sender_id = u.id AND c.receiver_id = ?
    WHERE u.role = 'admin'
    GROUP BY u.id, u.fullname, u.role
    ORDER BY u.fullname
  ");
  $q->bind_param("ii", $my_id, $my_id);
  $q->execute();
  $res = $q->get_result();
}

if(!$res || $res->num_rows==0){
  echo "<p>No messages</p>";
}

while($row = $res->fetch_assoc()){
  $unread = intval($row['unread'] ?? 0);
  $badge = $unread > 0 ? "<span class='badge'>{$unread}</span>" : "";
  $role = $row['role'] ?? '';
  echo "
  <div class='item'>
    <a href='chat.php?uid={$row['id']}'>
      {$row['fullname']}
      {$badge}
    </a>
    <span class='role'>".htmlspecialchars(ucfirst($role))."</span>
  </div>";
}
?>
</div>

<?php if ($my_role === 'admin'): ?>
  <?php include __DIR__ . "/admin_layout_end.php"; ?>
<?php endif; ?>

</body>
</html>
