<?php
include __DIR__ . "/db_connect.php";

$id = intval($_GET['collector_id']);

$q = $conn->query("
    SELECT lat, lng
    FROM collector_locations
    WHERE collector_id = $id
    ORDER BY created_at DESC
    LIMIT 20
");

$path = [];
while($r = $q->fetch_assoc()){
    $path[] = [$r['lat'], $r['lng']];
}

echo json_encode(array_reverse($path));
