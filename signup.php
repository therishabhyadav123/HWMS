<?php
session_start();
include __DIR__ . "/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.html");
    exit;
}

/* ===== Get Data ===== */
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = trim($_POST['role'] ?? '');

/* ===== Validation ===== */
if ($fullname === '' || $email === '' || $password === '' || $confirm === '' || $role === '') {
    echo "<script>alert('All fields are required'); window.history.back();</script>";
    exit;
}

if (!preg_match("/@gmail\.com$/", $email)) {
    echo "<script>alert('Only @gmail.com emails allowed'); window.history.back();</script>";
    exit;
}

if ($password !== $confirm) {
    echo "<script>alert('Passwords do not match'); window.history.back();</script>";
    exit;
}

/* Allowed roles */
$allowed_roles = ['user', 'admin', 'collector'];
if (!in_array($role, $allowed_roles)) {
    echo "<script>alert('Invalid role selected'); window.history.back();</script>";
    exit;
}

/* ===== Email Exists Check ===== */
$check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo "<script>alert('Email already exists. Please login.'); window.location.href='login.html';</script>";
    exit;
}

/* ===== Insert User ===== */
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $fullname, $email, $hashed, $role);

if ($stmt->execute()) {
    echo "<script>alert('Signup successful! Please login.'); window.location.href='login.html';</script>";
    exit;
} else {
    echo "<script>alert('Something went wrong'); window.history.back();</script>";
    exit;
}
?>
