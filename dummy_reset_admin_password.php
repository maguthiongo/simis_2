<?php
require_once("config/db.php");

// Desired new password
$newPassword = 'admin123';

// Hash it securely
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the database
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashedPassword);

if ($stmt->execute()) {
    echo "<h3 style='color: green;'>✅ Admin password reset to 'admin123' successfully.</h3>";
    echo "<pre>New Hashed Password: $hashedPassword</pre>";
} else {
    echo "<h3 style='color: red;'>❌ Failed to update password: " . $conn->error . "</h3>";
}

$stmt->close();
$conn->close();
