<?php
session_start();

$allowed = ['en', 'hi', 'hinglish'];
if (isset($_POST['lang']) && in_array($_POST['lang'], $allowed, true)) {
    $_SESSION['lang'] = $_POST['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect);
exit;
