<?php
include __DIR__ . "/db_connect.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Export PDF</title>
</head>
<body onload="window.print()">

<h2>Waste Report Summary</h2>

<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <th>ID</th><th>User</th><th>Type</th><th>Qty</th><th>Status</th><th>Date</th>
</tr>

<?php
$res = $conn->query("
    SELECT wr.*, u.fullname FROM waste_reports wr 
    JOIN users u ON wr.user_id = u.id
");

while($r = $res->fetch_assoc()):
?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= $r['fullname'] ?></td>
    <td><?= $r['waste_type'] ?></td>
    <td><?= $r['quantity'] ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= $r['created_at'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
