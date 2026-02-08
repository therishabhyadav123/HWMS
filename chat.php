<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$my_id = $_SESSION['user']['id'];
$receiver_id = intval($_GET['uid'] ?? 0);
$my_role = $_SESSION['user']['role'] ?? '';

if ($receiver_id == 0) {
    header("Location: user_dashboard.php"); // ya inbox page
    exit;
}

// Access control: users/collectors can only chat with admin, admin can chat with anyone
if ($my_role !== 'admin') {
    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
    $chk->bind_param("i", $receiver_id);
    $chk->execute();
    $ok = $chk->get_result()->num_rows > 0;
    $chk->close();
    if (!$ok) {
        header("Location: user_inbox.php");
        exit;
    }
}

$receiver_name = '';
$nameStmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ? LIMIT 1");
$nameStmt->bind_param("i", $receiver_id);
$nameStmt->execute();
$receiver = $nameStmt->get_result()->fetch_assoc();
$nameStmt->close();
$receiver_name = $receiver ? ($receiver['fullname'] . " (" . ucfirst($receiver['role']) . ")") : "Chat";
?>


<!DOCTYPE html>
<html>
<head>
<title>Chat</title>
<link rel="stylesheet" href="chat.css">
</head>
<body>

<div class="chat-container">
  <div class="chat-header">
    <h3><?= htmlspecialchars($receiver_name); ?></h3>
  </div>

  <div id="chatBox"></div>

  <form id="chatForm">
    <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
    <input type="text" name="message" id="message" placeholder="Type message..." required>
    <button type="submit">Send</button>
  </form>
</div>

<script>
const myId = <?= $my_id ?>;
const receiverId = <?= $receiver_id ?>;

function loadChat(){
  fetch(`fetch_messages.php?me=${myId}&other=${receiverId}`)
  .then(res=>res.text())
  .then(data=>{
    document.getElementById("chatBox").innerHTML = data;
    document.getElementById("chatBox").scrollTop = 9999;
  });
}
setInterval(loadChat,1000);
loadChat();

document.getElementById("chatForm").onsubmit = function(e){
  e.preventDefault();
  fetch("send_message.php",{
    method:"POST",
    body:new FormData(this)
  });
  document.getElementById("message").value="";
};
</script>

</body>
</html>
