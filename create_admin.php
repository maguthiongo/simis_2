<?php
require_once("config/db.php");

$username = 'admin';
$email = 'admin@example.com';
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = 'Admin';

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $username, $email, $password, $role);
    if ($insert->execute()) {
        echo "✅ Admin user created successfully.";
    } else {
        echo "❌ Failed to create admin user.";
    }
    $insert->close();
} else {
    echo "ℹ️ Admin user already exists.";
}

$stmt->close();
?>
