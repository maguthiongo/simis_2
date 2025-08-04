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

$delete = "DELETE FROM members WHERE id = $id";
if (mysqli_query($conn, $delete)) {
    header("Location: members.php?msg=deleted");
    exit;
} else {
    echo "Error deleting member: " . mysqli_error($conn);
}
