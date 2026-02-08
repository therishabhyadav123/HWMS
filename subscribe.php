<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user'])) {
    header("Location: signup.html");
    exit;
}

/* -------- PLAN DATA -------- */
$plan     = $_POST['plan'];
$price    = $_POST['price'];      // in INR
$duration = $_POST['duration'];   // days

$_SESSION['plan_data'] = [
    'plan'     => $plan,
    'price'    => $price,
    'duration' => $duration
];

/* -------- RAZORPAY KEYS (TEST MODE) -------- */
$keyId     = "rzp_test_w1AOm";
$keySecret = "f3LsjFbSR6pouK";

/* -------- CREATE ORDER -------- */
$orderData = [
    'receipt'         => 'rcpt_' . rand(1000,9999),
    'amount'          => $price * 100, // INR → paise
    'currency'        => 'INR',
    'payment_capture' => 1
];

$ch = curl_init("https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ":" . $keySecret);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$order_id = $response['id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Redirecting to Payment</title>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

<script>
var options = {
    "key": "<?= $keyId ?>",
    "amount": "<?= $price * 100 ?>",
    "currency": "INR",
    "name": "MediEco",
    "description": "<?= $plan ?> Plan Subscription",
    "order_id": "<?= $order_id ?>",
    "handler": function (response){
        window.location.href =
        "payment_success.php?payment_id=" + response.razorpay_payment_id +
        "&order_id=" + response.razorpay_order_id +
        "&signature=" + response.razorpay_signature;
    },
    "theme": {
        "color": "#1e6de0"
    }
};
var rzp = new Razorpay(options);
rzp.open();
</script>

</body>
</html>


