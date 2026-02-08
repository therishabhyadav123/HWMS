<?php
session_start();

if (!isset($_POST['credential'])) {
    die("Missing Google Credential");
}

$id_token = $_POST['credential'];

// Google token verification API (no library needed)
$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$payload = json_decode($response, true);

if (!isset($payload['email'])) {
    die("Invalid ID Token");
}

$email = $payload['email'];
$name  = $payload['name'] ?? "Google User";

$conn = new mysqli("localhost", "root", "", "waste_db");

// Check user exists
$stmt = $conn->prepare("SELECT id, fullname, role FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {

    $role = "user";  // default
    $insert = $conn->prepare("INSERT INTO users(fullname, email, role) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $name, $email, $role);
    $insert->execute();
    $id = $insert->insert_id;

} else {
    $stmt->bind_result($id, $fullname, $role);
    $stmt->fetch();
}

$_SESSION['user'] = [
    "id" => $id,
    "fullname" => $name,
    "email" => $email,
    "role" => $role
];

// Redirect
if ($role === "admin") {
    header("Location: admin_dashboard.php");
} else {
    header("Location: user_dashboard.php");
}
exit;
?>
