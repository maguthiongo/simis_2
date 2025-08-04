<?php
require_once('../config/db.php');
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    die("Access denied.");
}

$id = $_GET['id'] ?? null;
$membership_no = $_GET['membership_no'] ?? '';

if (!$id) {
    die("Transaction ID is missing.");
}

// Get transaction to confirm it exists and get the membership_no
$stmt = $conn->prepare("SELECT membership_no FROM transactions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Transaction not found.");
}

$membership_no = $transaction['membership_no']; // ensure accurate redirection

// Delete the transaction
$deleteStmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {
    echo "<script>
        alert('Transaction deleted.');
        window.location.href = 'member_statement.php?membership_no=" . urlencode($membership_no) . "';
    </script>";
    exit;
} else {
    echo "<script>
        alert('Failed to delete transaction: " . $conn->error . "');
        window.location.href = 'member_statement.php?membership_no=" . urlencode($membership_no) . "';
    </script>";
    exit;
}
?>
