<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    echo "Access denied.";
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);
$sql = "DELETE FROM loans WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: loans.php?msg=deleted");
    exit;
} else {
    echo "Error deleting loan: " . mysqli_error($conn);
}
