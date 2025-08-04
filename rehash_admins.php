<?php
require_once("config/db.php");

$updated = 0;

$users = $conn->query("SELECT id, password FROM users WHERE role IN ('Admin', 'Staff')");

while ($user = $users->fetch_assoc()) {
    $id = $user['id'];
    $password = $user['password'];

    // If not already hashed
    if (password_get_info($password)['algo'] === 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$hashed' WHERE id = $id");
        $updated++;
    }
}

echo "✅ Hashed $updated admin/staff passwords.";
?>
