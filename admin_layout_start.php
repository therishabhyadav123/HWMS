<?php
if (!isset($lang)) {
    $lang = $_SESSION['lang'] ?? 'en';
}
if (!isset($T)) {
    $T = include __DIR__ . "/languages/$lang.php";
}
$activePage = $activePage ?? '';
?>
<div class="dashboard">
  <aside class="sidebar">
    <div class="brand">
      <span class="logo"><i class="fa-solid fa-shield-heart"></i></span>
      EcoMed
    </div>
    <nav class="nav">
      <a class="<?= $activePage === 'dashboard' ? 'active' : ''; ?>" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> <?= $T['dashboard'] ?? 'Dashboard'; ?></a>
      <a class="<?= $activePage === 'reports' ? 'active' : ''; ?>" href="reports.php"><i class="fa-solid fa-clipboard"></i> <?= $T['reports'] ?? 'Reports'; ?></a>
      <a class="<?= $activePage === 'collectors' ? 'active' : ''; ?>" href="collectors.php"><i class="fa-solid fa-truck"></i> <?= $T['collectors'] ?? 'Collectors'; ?></a>
      <a class="<?= $activePage === 'assignments' ? 'active' : ''; ?>" href="assignments.php"><i class="fa-solid fa-clipboard-check"></i> <?= $T['assignments'] ?? 'Assignments'; ?></a>
      <a class="<?= $activePage === 'analytics' ? 'active' : ''; ?>" href="analytics.php"><i class="fa-solid fa-chart-line"></i> <?= $T['analytics'] ?? 'Analytics'; ?></a>
      <a class="<?= $activePage === 'messages' ? 'active' : ''; ?>" href="admin_chat_hub.php"><i class="fa-solid fa-envelope"></i> <?= $T['messages'] ?? 'Messages'; ?></a>
      <a class="<?= $activePage === 'settings' ? 'active' : ''; ?>" href="settings.php"><i class="fa-solid fa-gear"></i> <?= $T['settings'] ?? 'Settings'; ?></a>
      <button class="js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode"><i class="fa-solid fa-moon"></i> <?= $T['dark_mode'] ?? 'Dark Mode'; ?></button>
    </nav>

    <div class="spacer"></div>

    <div class="profile-card">
      <div class="name"><?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? 'Admin'); ?></div>
      <div class="role">Admin</div>
    </div>
    <button class="btn btn-danger" onclick="window.location.href='index.html'"><i class="fa-solid fa-right-from-bracket"></i> <?= $T['logout'] ?? 'Logout'; ?></button>
  </aside>

  <main class="content">
