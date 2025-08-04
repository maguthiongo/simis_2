<?php
require_once("config/db.php");

$newPassword = 'admin123';  // Set your desired new password here
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the admin's password
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashedPassword);

if ($stmt->execute()) {
    echo "✅ Admin password updated successfully.";
} else {
    echo "❌ Failed to update password: " . $conn->error;
}

$stmt->close();
$conn->close();
