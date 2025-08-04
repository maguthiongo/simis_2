<?php
session_start();

// Only Admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied. Admins only.");
}

require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("User ID is missing.");
}

$id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $role, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: users.php");
    exit();
}

// Get user data
$stmt = mysqli_prepare($conn, "SELECT username, email, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $username, $email, $role);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - VOV SACCO</title>
    <style>
        body { font-family: Arial; background-color: #eef3f7; padding: 20px; }
        form { background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 500px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 15px; background-color: #0074D9; color: white; border: none; border-radius: 4px; }
        a { display: inline-block; margin-top: 15px; color: #0074D9; text-decoration: none; }
    </style>
</head>
<body>
    <h2>Edit User - VOV SACCO</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Role:</label>
        <select name="role">
            <option value="Admin" <?= $role === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="Staff" <?= $role === 'Staff' ? 'selected' : '' ?>>Staff</option>
            <option value="Member" <?= $role === 'Member' ? 'selected' : '' ?>>Member</option>
        </select>

        <button type="submit">Update User</button>
        <br><a href="users.php">&larr; Back to Users</a>
    </form>
</body>
</html>
