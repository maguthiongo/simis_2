<?php
require_once("config/db.php");

$inserted = 0;
$skipped = 0;

$members = $conn->query("SELECT membership_no, full_name, phone FROM members");

while ($m = $members->fetch_assoc()) {
    $username = $m['phone'];
    $password = password_hash($username, PASSWORD_DEFAULT); // phone as password
    $membership_no = $m['membership_no'];
    $email = $username . "@example.com"; // dummy email to satisfy NOT NULL

    // Check if already exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $skipped++;
        continue;
    }

    $sql = "INSERT INTO users (username, email, password, role, membership_no)
            VALUES ('$username', '$email', '$password', 'Member', '$membership_no')";
    if ($conn->query($sql)) {
        $inserted++;
    } else {
        echo "❌ Error inserting $username: " . $conn->error . "<br>";
    }
}

echo "✅ Done: $inserted members added. $skipped already existed.";
?>
