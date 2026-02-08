<?php
session_start();
include "db_connect.php";

$sender = intval($_SESSION['user']['id'] ?? 0);
$receiver = intval($_POST['receiver_id'] ?? 0);
$msg = trim($_POST['message'] ?? '');
$my_role = $_SESSION['user']['role'] ?? '';

if ($sender <= 0 || $receiver <= 0 || $msg === '') {
    exit;
}

// Access control: users/collectors can only message admin
if ($my_role !== 'admin') {
    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
    $chk->bind_param("i", $receiver);
    $chk->execute();
    $ok = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$ok) {
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO chat (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender, $receiver, $msg);
$stmt->execute();
$stmt->close();
