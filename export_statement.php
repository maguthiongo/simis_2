<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$membership_no = $_GET['membership_no'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!$membership_no || $format !== 'csv') {
    echo "Invalid request.";
    exit;
}

// Fetch transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE membership_no = ? ORDER BY transaction_date ASC, id ASC");
$stmt->bind_param("s", $membership_no);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=member_statement_' . $membership_no . '.csv');

$output = fopen('php://output', 'w');

// Write the column headers
fputcsv($output, ['ID', 'Date', 'Description', 'Deposit', 'Savings', 'Christmas Fund', 'Loan Issued', 'Principal Paid', 'Interest Paid', 'Loan Balance', 'Others']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['transaction_date'],
        $row['description'],
        $row['amount_deposited'],
        $row['savings'],
        $row['christmas_fund'],
        $row['loan_amount'],
        $row['loan_principal'],
        $row['interest_paid'],
        $row['loan_balance'],
        $row['others']
    ]);
}

fclose($output);
exit();
