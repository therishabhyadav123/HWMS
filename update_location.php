<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Only collector allowed */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'collector') {
    http_response_code(403);
    exit("Unauthorized");
}

/* Validate GPS data */
if (!isset($_POST['latitude'], $_POST['longitude'])) {
    http_response_code(400);
    exit("Missing GPS data");
}

$collector_id = $_SESSION['user']['id'];
$lat = $_POST['latitude'];
$lng = $_POST['longitude'];

/* Insert GPS point */
$stmt = $conn->prepare(
    "INSERT INTO collector_locations (collector_id, lat, lng) VALUES (?, ?, ?)"
);
$stmt->bind_param("idd", $collector_id, $lat, $lng);
$stmt->execute();
$stmt->close();

echo "OK";
