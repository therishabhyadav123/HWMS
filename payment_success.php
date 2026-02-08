<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user']) || !isset($_SESSION['plan_data'])) {
    header("Location: plan.html");
    exit;
}

$user_id = $_SESSION['user']['id'];
$plan    = $_SESSION['plan_data']['plan'];
$price   = $_SESSION['plan_data']['price'];
$days    = $_SESSION['plan_data']['duration'];

$payment_id = $_GET['payment_id'];
$order_id   = $_GET['order_id'];
$signature  = $_GET['signature'];

/* -------- SUBSCRIPTION DATES -------- */
$start = date("Y-m-d");
$end   = date("Y-m-d", strtotime("+$days days"));

/* -------- SAVE SUBSCRIPTION (DEMO / TEST MODE) -------- */
$conn->query("
INSERT INTO subscriptions
(user_id, plan, price, start_date, end_date, status, payment_mode, payment_id)
VALUES
('$user_id','$plan','$price','$start','$end','active','RAZORPAY_TEST','$payment_id')
");

/* -------- CLEAN SESSION -------- */
unset($_SESSION['plan_data']);

echo "<h2>Payment Successful ✅</h2>";
echo "<p>Plan: $plan</p>";
echo "<p>Payment ID: $payment_id</p>";
echo "<a href='user_dashboard.php'>Go to Dashboard</a>";
?>
