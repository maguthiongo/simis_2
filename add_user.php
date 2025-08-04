<?php
session_start();
require_once("config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo "Access denied.";
    exit;
}

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[@$!%*?&]/', $password)
    ) {
        $error = "❌ Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_at, password_changed) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->bind_param("sss", $username, $hashed, $role);
        if ($stmt->execute()) {
            $success = "✅ User added successfully.";
        } else {
            $error = "❌ Failed to add user. Username may already exist.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #eef2f5; }
        .box { max-width: 500px; margin:auto; background:white; padding:25px; border-radius:8px; box-shadow:0 0 10px #aaa; }
        input, select { width:100%; padding:10px; margin-top:8px; }
        button { width:100%; background:#004080; color:white; padding:10px; margin-top:20px; border:none; }
        .msg { text-align:center; margin-top:15px; }
        .error { color:red; }
        .success { color:green; }
    </style>
</head>
<body>
<div class="box">
    <h2>➕ Add New User</h2>
    <?php if ($success): ?><p class="msg success"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p class="msg error"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" id="password" onkeyup="validatePasswordStrength()" required>
        <small id="strengthMessage" style="color:red;"></small>

        <label>Role:</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="Admin">Admin</option>
            <option value="Staff">Staff</option>
        </select>

        <input type="checkbox" onclick="togglePassword()"> Show Password

        <button type="submit">Add User</button>
    </form>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
}

function validatePasswordStrength() {
    const pwd = document.getElementById("password").value;
    const msg = document.getElementById("strengthMessage");
    const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    if (!strong.test(pwd)) {
        msg.innerText = "❌ Password must include uppercase, lowercase, number, special character, and be at least 8 characters.";
    } else {
        msg.innerText = "✅ Password looks strong.";
        msg.style.color = "green";
    }
}
</script>
</body>
</html>
