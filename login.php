<?php
session_start();
include __DIR__ . "/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

/* ===== Get Data ===== */
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = trim($_POST['role'] ?? '');

if ($email === '' || $password === '' || $role === '') {
    echo "<script>alert('All fields are required'); window.history.back();</script>";
    exit;
}

/* ===== Fetch User ===== */
$stmt = $conn->prepare(
    "SELECT id, fullname, email, password, role, site_language FROM users WHERE email=? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<script>alert('User not found'); window.history.back();</script>";
    exit;
}

$user = $result->fetch_assoc();

/* ===== Password Verify ===== */
if (!password_verify($password, $user['password'])) {
    echo "<script>alert('Wrong password'); window.history.back();</script>";
    exit;
}

/* ===== Role Match (IMPORTANT) ===== */
if (strtolower($role) !== strtolower($user['role'])) {
    echo "<script>alert('Selected role does not match your account'); window.history.back();</script>";
    exit;
}

/* ===== Session Set ===== */
$_SESSION['user'] = [
    'id'       => $user['id'],
    'fullname' => $user['fullname'],
    'email'    => $user['email'],
    'role'     => $user['role']
];

$allowedLangs = ['en', 'hi', 'hinglish'];
if (!empty($user['site_language']) && in_array($user['site_language'], $allowedLangs, true)) {
    $_SESSION['lang'] = $user['site_language'];
} else {
    $_SESSION['lang'] = 'en';
}

/* ===== Redirect by Role ===== */
if ($user['role'] === 'admin') {
    header("Location: admin_dashboard.php");
} elseif ($user['role'] === 'collector') {
    header("Location: collector_dashboard.php");
} else {
    header("Location: user_dashboard.php");
}
exit;
?>
