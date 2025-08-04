<?php
session_start();
require_once("config/db.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = trim($_POST["current_password"]);
    $new = trim($_POST["new_password"]);
    $confirm = trim($_POST["confirm_password"]);

    if (
        strlen($new) < 8 ||
        !preg_match('/[A-Z]/', $new) ||
        !preg_match('/[a-z]/', $new) ||
        !preg_match('/[0-9]/', $new) ||
        !preg_match('/[@$!%*?&]/', $new)
    ) {
        $error = "❌ Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } elseif ($new !== $confirm) {
        $error = "❌ New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($current, $user['password'])) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update->bind_param("ss", $newHash, $username);
            if ($update->execute()) {
                // Mark password changed
                if (isset($_GET['first_login']) && $_GET['first_login'] == 1) {
                    $flag = $conn->prepare("UPDATE users SET password_changed = 1 WHERE username = ?");
                    $flag->bind_param("s", $username);
                    $flag->execute();
                    $flag->close();
                }

                $success = "✅ Password updated successfully.";
                
            } else {
                $error = "❌ Failed to update password.";
            }
            $update->close();
        } else {
            $error = "❌ Current password is incorrect.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial; background: #eef2f5; padding: 30px; }
        .box { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px #aaa; }
        input { width: 100%; padding: 10px; margin-top: 8px; }
        button { width: 100%; background: #004080; color: white; padding: 10px; margin-top: 20px; border: none; }
        .msg { text-align: center; margin-top: 15px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Change Password</h2>
    <?php if ($success): ?><p class="msg success"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p class="msg error"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>

        <label>New Password:</label>
        <input type="password" name="new_password" id="new_password" onkeyup="validatePasswordStrength()" required>
        <small id="strengthMessage" style="color:red;"></small>

        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>

        <input type="checkbox" onclick="togglePassword()"> Show Password

        <button type="submit">Update Password</button>
    </form>
</div>

<script>
function togglePassword() {
    document.querySelectorAll('input[type="password"]').forEach(pwd => {
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    });
}

function validatePasswordStrength() {
    const pwd = document.getElementById('new_password').value;
    const msg = document.getElementById('strengthMessage');
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
