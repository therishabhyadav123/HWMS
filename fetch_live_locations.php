<?php
session_start();
include __DIR__ . "/db_connect.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$q = $conn->query("
    SELECT u.id, u.fullname, cl.lat, cl.lng, cl.created_at
    FROM collector_locations cl
    JOIN users u ON cl.collector_id = u.id
    JOIN (
        SELECT collector_id, MAX(created_at) last_time
        FROM collector_locations
        GROUP BY collector_id
    ) x ON x.collector_id = cl.collector_id
       AND x.last_time = cl.created_at
");

$data = [];
while ($row = $q->fetch_assoc()) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
