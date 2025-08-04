<?php
session_start();
require_once('../config/db.php');

// Allow only Admin and Staff to delete
$role = strtolower($_SESSION['role'] ?? '');
if (!in_array($role, ['admin', 'staff'])) {
    echo "Access denied.";
    exit;
}

// Check if ID is passed in query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid ID.";
    exit;
}

$id = (int) $_GET['id'];

// Prepare delete query
$sql = "DELETE FROM expenses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Delete failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
