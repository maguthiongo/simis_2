<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    echo "Access denied.";
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM loans WHERE id = $id");

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Loan not found.";
    exit;
}

$loan = mysqli_fetch_assoc($result);
$message = "";

// Fetch member name
$member_result = mysqli_query($conn, "SELECT full_name FROM members WHERE membership_no = '{$loan['membership_no']}'");
$member = mysqli_fetch_assoc($member_result);
$member_name = $member['full_name'] ?? 'Unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membership_no = mysqli_real_escape_string($conn, $_POST['membership_no']);
    $loan_no = mysqli_real_escape_string($conn, $_POST['loan_no']);
    $loan_amount = floatval($_POST['loan_amount']);
    $issued_date = mysqli_real_escape_string($conn, $_POST['issued_date']);
    $monthly_installment = floatval($_POST['monthly_installment']);
    $monthly_interest = floatval($_POST['monthly_interest']);
    $loan_balance = floatval($_POST['loan_balance']);
    $loan_status = mysqli_real_escape_string($conn, $_POST['loan_status']);
    $no_of_installments = intval($_POST['no_of_installments']);

    // Check for duplicate loan_no
    $check = mysqli_query($conn, "SELECT id FROM loans WHERE loan_no = '$loan_no' AND id != $id");
    if (mysqli_num_rows($check) > 0) {
        $message = "<p style='color: red;'>Error: Loan No <strong>$loan_no</strong> is already used by another loan.</p>";
    } else {
        $sql = "UPDATE loans SET 
            membership_no = '$membership_no',
            loan_no = '$loan_no',
            loan_amount = $loan_amount,
            issued_date = '$issued_date',
            monthly_installment = $monthly_installment,
            monthly_interest = $monthly_interest,
            loan_balance = $loan_balance,
            loan_status = '$loan_status',
            no_of_installments = $no_of_installments
            WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: loans.php?success=1");
            exit;
        } else {
            $message = "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Loan</title></head>
<body>
    <h2>Edit Loan</h2>
    <form method="POST">
        <label>Membership No</label>
        <input type="text" name="membership_no" value="<?= htmlspecialchars($loan['membership_no']) ?>" required>
        
        <label>Member Name</label>
        <input type="text" value="<?= htmlspecialchars($member_name) ?>" readonly>

        <label>Loan No</label>
        <input type="text" name="loan_no" value="<?= htmlspecialchars($loan['loan_no']) ?>" required>

        <label>Loan Amount</label>
        <input type="number" name="loan_amount" value="<?= $loan['loan_amount'] ?>" required>

        <label>Issued Date</label>
        <input type="date" name="issued_date" value="<?= $loan['issued_date'] ?>" required>

        <label>Monthly Installment</label>
        <input type="number" name="monthly_installment" value="<?= $loan['monthly_installment'] ?>" required>

        <label>Monthly Interest</label>
        <input type="number" name="monthly_interest" value="<?= $loan['monthly_interest'] ?>" required>

        <label>Loan Balance</label>
        <input type="number" name="loan_balance" value="<?= $loan['loan_balance'] ?>" required>

        <label>Installment(s)</label>
        <input type="number" name="no_of_installments" value="<?= $loan['no_of_installments'] ?>" required>

        <label>Loan Status</label>
        <select name="loan_status">
            <?php
            $statuses = ['Active', 'Poor repayment', 'Defaulted', 'Cleared', 'Others'];
            foreach ($statuses as $status) {
                $selected = ($loan['loan_status'] == $status) ? 'selected' : '';
                echo "<option value=\"$status\" $selected>$status</option>";
            }
            ?>
        </select>
        <br><br>
        <button type="submit">Update Loan</button>
    </form>
    <?= $message ?>
</body>
</html>
