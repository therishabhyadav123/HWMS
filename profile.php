<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'] ?? 'user';
$dashboardUrl = 'user_dashboard.php';
if ($role === 'admin') {
    $dashboardUrl = 'admin_dashboard.php';
} elseif ($role === 'collector') {
    $dashboardUrl = 'collector_dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | MediEco</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="theme.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            inter: ['Inter', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="font-inter bg-[#f4f8ff] text-slate-900 min-h-screen">
  <header class="bg-white/80 backdrop-blur-xl border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <a href="index.php" class="inline-flex items-center gap-3 font-extrabold text-lg text-blue-700">
        <span class="w-11 h-11 rounded-full bg-blue-500/10 flex items-center justify-center">
          <i class="fa-solid fa-shield-heart text-teal-600"></i>
        </span>
        MediEco
      </a>
      <div class="flex items-center gap-3">
        <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition shadow">
          Dashboard
        </a>
        <a href="logout.php" class="px-4 py-2 rounded-full border border-slate-200 bg-white text-sm font-semibold hover:bg-slate-100 transition">
          Log Out
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-bold">
          <?php echo htmlspecialchars(strtoupper(substr($user['fullname'] ?? 'U', 0, 1))); ?>
        </div>
        <div>
          <h1 class="text-2xl font-extrabold"><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></h1>
          <p class="text-slate-500">Role: <?php echo htmlspecialchars(ucfirst($role)); ?></p>
        </div>
      </div>

      <div class="mt-8 grid gap-6 sm:grid-cols-2">
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
          <p class="text-sm text-slate-500">Email</p>
          <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
          <p class="text-sm text-slate-500">Account ID</p>
          <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($user['id'] ?? ''); ?></p>
        </div>
      </div>

      <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="px-5 py-2 rounded-full bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition shadow">
          Go to Dashboard
        </a>
        <?php if ($role === 'admin'): ?>
          <a href="settings.php" class="px-5 py-2 rounded-full border border-slate-200 bg-white text-sm font-semibold hover:bg-slate-100 transition">
            Admin Settings
          </a>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
<script src="theme.js"></script>
</html>
