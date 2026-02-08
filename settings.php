<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Admin only */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* Language load */
$lang = $_SESSION['lang'] ?? 'en';
$T = include __DIR__ . "/languages/$lang.php";

$admin_id = $_SESSION['user']['id'];
$message = "";

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $fullname, $email, $admin_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user']['fullname'] = $fullname;
    $_SESSION['user']['email'] = $email;

    $message = $T['profile_updated'] ?? 'Profile updated successfully';
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $q = $conn->prepare("SELECT password FROM users WHERE id=?");
    $q->bind_param("i", $admin_id);
    $q->execute();
    $q->bind_result($hash);
    $q->fetch();
    $q->close();

    if (!password_verify($current, $hash)) {
        $message = $T['wrong_password'] ?? 'Current password incorrect';
    } elseif ($new !== $confirm) {
        $message = $T['password_mismatch'] ?? 'Passwords do not match';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $u = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $u->bind_param("si", $newHash, $admin_id);
        $u->execute();
        $u->close();

        $message = $T['password_updated'] ?? 'Password updated successfully';
    }
}

/* =========================
   SAVE LANGUAGE SETTING
========================= */
if (isset($_POST['save_language'])) {
    $newLang = $_POST['site_language'];

    $_SESSION['lang'] = $newLang;

    $s = $conn->prepare("UPDATE users SET site_language=? WHERE id=?");
    $s->bind_param("si", $newLang, $admin_id);
    $s->execute();
    $s->close();

    $message = $T['language_updated'] ?? 'Language updated';
}

/* Fetch current admin data */
$admin = $conn->query("SELECT fullname, email, site_language FROM users WHERE id=$admin_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $T['settings'] ?? 'Settings'; ?> | MediEco</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="theme.css">
<link rel="stylesheet" href="dashboard_theme.css">

<style>
  :root{
    --bg:#f4f8ff;
    --panel:#ffffff;
    --panel-alt:#f8fafc;
    --text:#0f172a;
    --muted:#64748b;
    --brand:#1e6de0;
    --border:#e2e8f0;
    --shadow:0 18px 40px rgba(15,23,42,0.08);
    --radius:16px;
  }
  html[data-theme="dark"]{
    --bg:#0c1422;
    --panel:#0f1a2c;
    --panel-alt:#111f35;
    --text:#e2e8f0;
    --muted:#94a3b8;
    --brand:#4f8bff;
    --border:#1f2a44;
    --shadow:0 18px 40px rgba(0,0,0,0.35);
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    font-family:"Manrope",sans-serif;
    background:var(--bg);
    color:var(--text);
  }
  .page{
    min-height:100vh;
    padding:32px 16px 60px;
  }
  .container{
    max-width:980px;
    margin:0 auto;
  }
  .header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    margin-bottom:24px;
  }
  .header-title h1{
    margin:0;
    font-size:30px;
  }
  .header-title p{
    margin:6px 0 0;
    color:var(--muted);
  }
  .header-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  .btn{
    border:none;
    border-radius:999px;
    padding:10px 16px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }
  .btn-primary{
    background:var(--brand);
    color:#fff;
    box-shadow:0 10px 20px rgba(30,109,224,0.2);
  }
  .btn-outline{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
  }
  .grid{
    display:grid;
    gap:18px;
  }
  .card{
    background:var(--panel);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:22px;
  }
  .card h3{
    margin:0 0 8px;
    font-size:20px;
  }
  .card p{
    margin:0 0 18px;
    color:var(--muted);
  }
  .form-grid{
    display:grid;
    gap:14px;
  }
  label{
    display:block;
    font-weight:600;
    color:var(--muted);
    margin-bottom:6px;
  }
  input, select{
    width:100%;
    padding:12px 14px;
    border-radius:12px;
    border:1px solid var(--border);
    background:var(--panel-alt);
    color:var(--text);
    font-family:"Manrope",sans-serif;
  }
  .alert{
    background:rgba(34,197,94,0.12);
    border:1px solid rgba(34,197,94,0.35);
    color:#0f5132;
    padding:12px 14px;
    border-radius:12px;
    margin-bottom:18px;
    font-weight:600;
  }
  html[data-theme="dark"] .alert{
    color:#bbf7d0;
  }
  @media(max-width:720px){
    .header{flex-direction:column; align-items:flex-start;}
  }
</style>
</head>

<body>
<?php $activePage = 'settings'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="page">
  <div class="container">
    <div class="header">
      <div class="header-title">
        <h1><?= $T['settings'] ?? 'Settings'; ?></h1>
        <p>Manage your profile, security, and language preferences.</p>
      </div>
      <div class="header-actions">
        <a href="admin_dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        <button class="btn btn-primary js-theme-toggle" data-dark-label="Dark Mode" data-light-label="Light Mode">
          <i class="fa-solid fa-moon"></i> Theme
        </button>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="grid">
      <!-- PROFILE SETTINGS -->
      <div class="card">
        <h3><?= $T['profile_settings'] ?? 'Profile Settings'; ?></h3>
        <p>Update your account details so your team can reach you.</p>
        <form method="POST" class="form-grid">
          <div>
            <label><?= $T['fullname'] ?? 'Full Name'; ?></label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($admin['fullname']); ?>" required>
          </div>
          <div>
            <label><?= $T['email'] ?? 'Email'; ?></label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" required>
          </div>
          <div>
            <button class="btn btn-primary" name="update_profile"><?= $T['save'] ?? 'Save'; ?></button>
          </div>
        </form>
      </div>

      <!-- PASSWORD SETTINGS -->
      <div class="card">
        <h3><?= $T['change_password'] ?? 'Change Password'; ?></h3>
        <p>Keep your account secure with a strong, unique password.</p>
        <form method="POST" class="form-grid">
          <div>
            <label><?= $T['current_password'] ?? 'Current Password'; ?></label>
            <input type="password" name="current_password" required>
          </div>
          <div>
            <label><?= $T['new_password'] ?? 'New Password'; ?></label>
            <input type="password" name="new_password" required>
          </div>
          <div>
            <label><?= $T['confirm_password'] ?? 'Confirm Password'; ?></label>
            <input type="password" name="confirm_password" required>
          </div>
          <div>
            <button class="btn btn-primary" name="change_password"><?= $T['update'] ?? 'Update'; ?></button>
          </div>
        </form>
      </div>

      <!-- LANGUAGE SETTINGS -->
      <div class="card">
        <h3><?= $T['language_settings'] ?? 'Language Settings'; ?></h3>
        <p>Select the language you want to use across the platform.</p>
        <form method="POST" class="form-grid">
          <div>
            <label><?= $T['select_language'] ?? 'Select Language'; ?></label>
            <select name="site_language">
              <option value="en" <?= $admin['site_language']=='en'?'selected':'' ?>>English</option>
              <option value="hi" <?= $admin['site_language']=='hi'?'selected':'' ?>>हिंदी</option>
              <option value="hinglish" <?= $admin['site_language']=='hinglish'?'selected':'' ?>>Hinglish</option>
            </select>
          </div>
          <div>
            <button class="btn btn-primary" name="save_language"><?= $T['save'] ?? 'Save'; ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>
