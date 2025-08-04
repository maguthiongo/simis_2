<?php
session_start();

// Dummy user database (replace with actual DB later)
$users = [
    'admin' => ['password' => 'admin123', 'role' => 'Admin'],
    'staff1' => ['password' => 'staff123', 'role' => 'Staff'],
    'member1' => ['password' => 'member123', 'role' => 'Member']
];

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $users[$username]['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=Invalid username or password");
        exit();
    }
} else {
    header("Location: login.php?error=Please enter login details");
    exit();
}
