<?php
require_once("config/db.php");

$username = 'admin';
$entered_password = 'admin123';

// Fetch user from DB
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $stored_hash = $user['password'];

    echo "<strong>Username:</strong> {$user['username']}<br>";
    echo "<strong>Stored Hash:</strong> {$stored_hash}<br>";

    if (password_verify($entered_password, $stored_hash)) {
        echo "<span style='color:green;'>✅ Password is correct.</span>";
    } else {
        echo "<span style='color:red;'>❌ Password is INCORRECT.</span>";
    }
} else {
    echo "<span style='color:red;'>❌ User not found in the database.</span>";
}
?>
