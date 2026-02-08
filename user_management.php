<?php
session_start();
include __DIR__ . "/db_connect.php";

// Allow ONLY admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// CREATE User (Admin can create admin, collector, user)
if (isset($_POST['create_user'])) {

    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];  // admin / collector / user
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.history.back();</script>";
        exit;
    }

    // Insert
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $password, $role);
    $stmt->execute();

    echo "<script>alert('Account Created Successfully!'); window.location.href='user_management.php';</script>";
    exit;
}

// DELETE User
if (isset($_POST['delete_user'])) {

    $uid = intval($_POST['user_id']);

    // Admin cannot delete itself
    if ($uid == $_SESSION['user']['id']) {
        echo "<script>alert('You cannot delete your own admin account!');</script>";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    echo "<script>alert('User Deleted Successfully'); window.location.href='user_management.php';</script>";
    exit;
}

// Fetch All Users
$users = $conn->query("SELECT * FROM users ORDER BY role, fullname");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_theme.css">
    <style>
        body { font-family: "Manrope","Segoe UI",Arial,sans-serif; margin:0; }
        .container { width: 90%; margin: 20px auto; background:#fff; padding:20px; border-radius:10px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:12px; border-bottom:1px solid #ddd; }
        th { background:#1e6de0; color:white; }
        .btn { padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
        .create-btn { background:#1e6de0; color:white; margin-top:10px; }
        .delete-btn { background:#e63946; color:white; }
        .form-box { width:340px; background:#eef9f3; padding:15px; border-radius:8px; margin-bottom:20px; }
        input, select { width:100%; padding:10px; margin-top:8px; border-radius:6px; border:1px solid #ccc; }
    </style>
</head>

<body>

<?php $activePage = 'user_management'; include __DIR__ . "/admin_layout_start.php"; ?>
<div class="container">
    <h2>User Management (Admin Only)</h2>

    <!-- Create New User Box -->
    <div class="form-box">
        <h3>Create New User</h3>
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="collector">Collector</option>
                <option value="user">User</option>
            </select>

            <input type="password" name="password" placeholder="Password" required>

            <button class="btn create-btn" name="create_user">Create User</button>
        </form>
    </div>

    <!-- Display Users Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Fullname</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>

        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['fullname']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>

                <td>
                    <form method="POST" onsubmit="return confirm('Delete this user?')">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button class="btn delete-btn" name="delete_user">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>

