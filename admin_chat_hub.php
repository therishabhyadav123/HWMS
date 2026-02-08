<?php
session_start();
include __DIR__ . "/db_connect.php";

// Admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = intval($_SESSION['user']['id']);

// Fetch users + collectors with last message + unread count
$stmt = $conn->prepare("
    SELECT
      u.id,
      u.fullname,
      u.role,
      lm.message,
      lm.sender_id,
      lm.receiver_id,
      lm.id AS message_id,
      SUM(CASE WHEN c.receiver_id = ? AND c.is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM users u
    LEFT JOIN (
      SELECT c1.*
      FROM chat c1
      JOIN (
        SELECT
          CASE
            WHEN sender_id = ? THEN receiver_id
            ELSE sender_id
          END AS other_id,
          MAX(id) AS max_id
        FROM chat
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY other_id
      ) last ON c1.id = last.max_id
    ) lm ON lm.sender_id = u.id OR lm.receiver_id = u.id
    LEFT JOIN chat c ON c.sender_id = u.id AND c.receiver_id = ? AND c.is_read = 0
    WHERE u.role IN ('user','collector')
    GROUP BY u.id, u.fullname, u.role, lm.message, lm.sender_id, lm.receiver_id, lm.id
    ORDER BY (lm.id IS NULL), lm.id DESC, u.fullname
");
$stmt->bind_param("iiiii", $admin_id, $admin_id, $admin_id, $admin_id, $admin_id);
$stmt->execute();
$list = $stmt->get_result();

$activePage = 'messages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Chat Hub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="dashboard_theme.css">
  <style>
    .chat-hub{
      display:grid;
      grid-template-columns: minmax(260px, 340px) 1fr;
      gap:18px;
    }
    .chat-list{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .chat-list-header{
      padding:16px;
      border-bottom:1px solid var(--border);
      display:flex;
      gap:10px;
      align-items:center;
    }
    .chat-search{
      flex:1;
      display:flex;
      align-items:center;
      gap:8px;
      background:var(--panel-alt);
      border:1px solid var(--border);
      border-radius:12px;
      padding:8px 10px;
    }
    .chat-search input{
      border:none;
      background:transparent;
      width:100%;
      outline:none;
      color:var(--text);
      font-family:"Manrope",sans-serif;
    }
    .chat-item{
      padding:14px 16px;
      border-bottom:1px solid var(--border);
      cursor:pointer;
      display:flex;
      gap:10px;
      align-items:flex-start;
      transition:background 0.2s ease;
    }
    .chat-item:hover{background:rgba(30,109,224,0.06);}
    .chat-avatar{
      width:38px;
      height:38px;
      border-radius:12px;
      display:grid;
      place-items:center;
      font-weight:700;
      color:#fff;
      background:linear-gradient(135deg, var(--brand), #63b3ff);
    }
    .chat-meta{flex:1;}
    .chat-name{
      font-weight:700;
      display:flex;
      align-items:center;
      gap:8px;
    }
    .chat-role{
      font-size:12px;
      color:var(--muted);
    }
    .chat-preview{
      color:var(--muted);
      font-size:13px;
      margin-top:6px;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      max-width:230px;
    }
    .chat-badge{
      background:var(--danger);
      color:#fff;
      border-radius:999px;
      padding:2px 8px;
      font-size:12px;
      font-weight:700;
    }
    .chat-empty{
      padding:18px;
      color:var(--muted);
    }
    .chat-preview-card{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      padding:22px;
      display:flex;
      flex-direction:column;
      gap:14px;
      min-height:240px;
    }
    .chat-preview-card h3{margin:0;}
    .chat-preview-card p{margin:0;color:var(--muted);}
    .open-chat{
      margin-top:auto;
      align-self:flex-start;
    }
    @media (max-width: 980px){
      .chat-hub{grid-template-columns:1fr;}
      .chat-preview{max-width:100%;}
    }
  </style>
</head>
<body>
<?php include __DIR__ . "/admin_layout_start.php"; ?>

  <div class="page-header">
    <div class="page-title">
      <h1>Admin Chat Hub</h1>
      <p>Search conversations and jump into chats with users or collectors.</p>
    </div>
  </div>

  <div class="chat-hub">
    <div class="chat-list">
      <div class="chat-list-header">
        <div class="chat-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input id="chatSearch" type="text" placeholder="Search by name or role...">
        </div>
      </div>

      <div id="chatList">
        <?php if ($list && $list->num_rows): ?>
          <?php while ($row = $list->fetch_assoc()): ?>
            <?php
              $name = $row['fullname'] ?? 'Unknown';
              $role = $row['role'] ?? 'user';
              $initial = strtoupper(substr($name, 0, 1));
              $unread = intval($row['unread'] ?? 0);
              $preview = $row['message'] ?? 'No messages yet';
            ?>
            <div class="chat-item" data-name="<?= htmlspecialchars(strtolower($name)); ?>" data-role="<?= htmlspecialchars(strtolower($role)); ?>" data-chat-url="chat.php?uid=<?= intval($row['id']); ?>">
              <div class="chat-avatar"><?= htmlspecialchars($initial); ?></div>
              <div class="chat-meta">
                <div class="chat-name">
                  <?= htmlspecialchars($name); ?>
                  <span class="chat-role">(<?= htmlspecialchars(ucfirst($role)); ?>)</span>
                </div>
                <div class="chat-preview"><?= htmlspecialchars($preview); ?></div>
              </div>
              <?php if ($unread > 0): ?>
                <div class="chat-badge"><?= $unread; ?></div>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="chat-empty">No conversations yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="chat-preview-card" id="previewCard">
      <h3>Select a conversation</h3>
      <p>Pick a user or collector from the list to open the chat.</p>
      <a id="openChatBtn" class="btn btn-primary open-chat" href="#" style="display:none;">
        <i class="fa-solid fa-comment-dots"></i> Open Chat
      </a>
    </div>
  </div>

<?php include __DIR__ . "/admin_layout_end.php"; ?>

<script>
const search = document.getElementById('chatSearch');
const items = Array.from(document.querySelectorAll('.chat-item'));
const openChatBtn = document.getElementById('openChatBtn');
const previewCard = document.getElementById('previewCard');

items.forEach(item => {
  item.addEventListener('click', () => {
    const url = item.getAttribute('data-chat-url');
    const name = item.querySelector('.chat-name').childNodes[0].textContent.trim();
    const role = item.querySelector('.chat-role').textContent.trim();
    const preview = item.querySelector('.chat-preview').textContent.trim();

    previewCard.querySelector('h3').textContent = name;
    previewCard.querySelector('p').textContent = `${role} · ${preview}`;
    openChatBtn.style.display = 'inline-flex';
    openChatBtn.href = url;
  });
});

search.addEventListener('input', () => {
  const q = search.value.trim().toLowerCase();
  items.forEach(item => {
    const name = item.getAttribute('data-name');
    const role = item.getAttribute('data-role');
    const match = name.includes(q) || role.includes(q);
    item.style.display = match ? 'flex' : 'none';
  });
});
</script>
</body>
</html>
