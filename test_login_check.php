<?php
require_once("config/db.php");

$username = 'admin';
$passwordInput = 'admin123';

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "🔍 Username: " . $user['username'] . "<br>";
    echo "🔐 Hashed Password: " . $user['password'] . "<br>";

    if (password_verify($passwordInput, $user['password'])) {
        echo "<h3 style='color: green;'>✅ Password MATCHES!</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ Password does NOT match!</h3>";
    }
} else {
    echo "<h3 style='color: red;'>❌ User not found</h3>";
}
?>
