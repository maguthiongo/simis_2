<?php
require_once('../config/db.php');
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    die("Access denied.");
}

$id = $_GET['id'] ?? null;
$error = '';
$transaction = null;

// Step 1: Fetch transaction
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    if (!$transaction) die("Transaction not found.");
} else {
    die("Transaction ID missing.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>Form was submitted.</pre>";

    $transaction_date  = $_POST['transaction_date'] ?? '';
    $description       = $_POST['description'] ?? '';
    $amount_deposited  = isset($_POST['amount_deposited']) ? floatval($_POST['amount_deposited']) : 0;
    $savings           = isset($_POST['savings']) ? floatval($_POST['savings']) : 0;
    $christmas_fund    = isset($_POST['christmas_fund']) ? floatval($_POST['christmas_fund']) : 0;
    $loan_amount       = isset($_POST['loan_amount']) ? floatval($_POST['loan_amount']) : 0;
    $loan_principal    = isset($_POST['loan_principal']) ? floatval($_POST['loan_principal']) : 0;
    $interest_paid     = isset($_POST['interest_paid']) ? floatval($_POST['interest_paid']) : 0;
    $loan_balance      = isset($_POST['loan_balance']) ? floatval($_POST['loan_balance']) : 0;
    $others            = isset($_POST['others']) ? floatval($_POST['others']) : 0;

    // Show debug of posted data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $expected = $savings + $christmas_fund + $loan_principal + $interest_paid + $others;

    if (trim($transaction_date) === '') {
        $error = "Transaction date is required.";
    } elseif ($loan_amount <= 0 && abs($amount_deposited - $expected) > 0.01) {
        $error = "Deposit does not match components! Expected: " . number_format($expected, 2);
    }

    if (!$error) {
        echo "<pre>Preparing to update ID: $id</pre>";

        $stmt = $conn->prepare("UPDATE transactions SET 
            transaction_date=?, description=?, amount_deposited=?, savings=?, 
            christmas_fund=?, loan_amount=?, loan_principal=?, interest_paid=?, 
            loan_balance=?, others=? WHERE id=?");

        if (!$stmt) {
            echo "<pre>Prepare failed: " . $conn->error . "</pre>";
            exit;
        }

        $stmt->bind_param("ssdddddddii",
            $transaction_date, $description, $amount_deposited, $savings,
            $christmas_fund, $loan_amount, $loan_principal, $interest_paid,
            $loan_balance, $others, $id
        );

        if ($stmt->execute()) {
            echo "<script>
                alert('Transaction updated successfully.');
                window.opener.location.reload();
                window.close();
            </script>";
            exit;
        } else {
            echo "<pre>Execution failed: " . $stmt->error . "</pre>";
        }
    } else {
        echo "<pre>Validation error: $error</pre>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaction</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        label { display: block; margin-top: 10px; }
        input[type=text], input[type=number], input[type=date] {
            width: 100%; padding: 8px; margin-top: 5px;
        }
        .btn { margin-top: 15px; padding: 8px 16px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Edit Transaction #<?= htmlspecialchars($transaction['id']) ?></h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Transaction Date:</label>
        <input type="date" name="transaction_date" value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required>

        <label>Description:</label>
        <input type="text" name="description" value="<?= htmlspecialchars($transaction['description'] ?? '') ?>">

        <label>Amount Deposited:</label>
        <input type="number" step="0.01" name="amount_deposited" value="<?= $transaction['amount_deposited'] ?? 0 ?>" required>

        <label>Savings:</label>
        <input type="number" step="0.01" name="savings" value="<?= $transaction['savings'] ?? 0 ?>" required>

        <label>Christmas Fund:</label>
        <input type="number" step="0.01" name="christmas_fund" value="<?= $transaction['christmas_fund'] ?? 0 ?>" required>

        <label>Issued Loan:</label>
        <input type="number" step="0.01" name="loan_amount" value="<?= $transaction['loan_amount'] ?? 0 ?>">

        <label>Loan Principal Paid:</label>
        <input type="number" step="0.01" name="loan_principal" value="<?= $transaction['loan_principal'] ?? 0 ?>" required>

        <label>Interest Paid:</label>
        <input type="number" step="0.01" name="interest_paid" value="<?= $transaction['interest_paid'] ?? 0 ?>" required>

        <label>Loan Balance:</label>
        <input type="number" step="0.01" name="loan_balance" value="<?= $transaction['loan_balance'] ?? 0 ?>" required>

        <label>Others (if any):</label>
        <input type="number" step="0.01" name="others" value="<?= $transaction['others'] ?? 0 ?>">

        <button class="btn" type="submit">Save Changes</button>
    </form>
</body>
</html>
