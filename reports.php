<?php
session_start();
include 'db_connect.php';

/* Login check */
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

/* Fetch reports */
$query = "SELECT * FROM waste_reports ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>All Reports</title>

<!-- Auto refresh every 5 seconds -->
<meta http-equiv="refresh" content="5">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">

<style>
body {
    font-family: "Manrope","Segoe UI",Arial,sans-serif;
    margin: 0;
}
.container {
    width: 90%;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
h2 {
    text-align: center;
    color: #2c3e50;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
table th {
    background: #2c3e50;
    color: white;
    padding: 10px;
}
table td {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}
.btn {
    padding: 7px 12px;
    background: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
}
.btn:hover {
    background: #0056b3;
}
</style>
</head>

<body>
<?php $activePage = 'reports'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="container">
    <h2>All Waste Disposal Reports (Live)</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Waste Type</th>
            <th>User</th>
            <th>Status</th>
            <th>Date</th>
            <th>Certificate</th>
        </tr>

        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['waste_type']; ?></td>
            <td><?= $row['user_name']; ?></td>
            <td><?= $row['status']; ?></td>
            <td><?= $row['created_at']; ?></td>
            <td>
                <a href="generate_certificate.php?id=<?= $row['id']; ?>" class="btn">
                    Generate 
                </a>
            </td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>No reports found</td></tr>";
        }
        ?>
    </table>
</div>
<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>
