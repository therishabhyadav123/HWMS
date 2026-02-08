<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user'])) {
    exit;
}

$me    = intval($_SESSION['user']['id']);   // logged in user
$other = intval($_GET['other']);             // jisse chat hai
$my_role = $_SESSION['user']['role'] ?? '';

if ($other <= 0) {
    exit;
}

// Access control: users/collectors can only chat with admin
if ($my_role !== 'admin') {
    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
    $chk->bind_param("i", $other);
    $chk->execute();
    $ok = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$ok) {
        exit;
    }
}

$stmt = $conn->prepare("
 SELECT sender_id, receiver_id, message
 FROM chat
 WHERE (sender_id = ? AND receiver_id = ?)
    OR (sender_id = ? AND receiver_id = ?)
 ORDER BY id ASC
");
$stmt->bind_param("iiii", $me, $other, $other, $me);
$stmt->execute();
$res = $stmt->get_result();

// mark messages as read
$conn->query("UPDATE chat SET is_read = 1 WHERE receiver_id = $me AND sender_id = $other");

while ($row = $res->fetch_assoc()) {
    $cls = ($row['sender_id'] == $me) ? 'me' : 'other';
    echo "<div class='msg $cls'>".htmlspecialchars($row['message'])."</div>";
}

