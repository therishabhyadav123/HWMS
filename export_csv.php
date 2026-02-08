<?php
include __DIR__ . "/db_connect.php";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=waste_reports.csv");

$output = fopen("php://output", "w");

fputcsv($output, ["ID","User","Waste Type","Qty","Description","Status","Date"]);

$res = $conn->query("
    SELECT wr.*, u.fullname FROM waste_reports wr
    JOIN users u ON wr.user_id = u.id
");

while ($r = $res->fetch_assoc()) {
    fputcsv($output, [
        $r['id'],
        $r['fullname'],
        $r['waste_type'],
        $r['quantity'],
        $r['description'],
        $r['status'],
        $r['created_at']
    ]);
}

fclose($output);
exit;
?>
