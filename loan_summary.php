<?php
session_start();

// Simple check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo "Access denied. Admins only.";
    exit;
}

require_once('../config/db.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loan Summary - VOV SACCO</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        tfoot td { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h2>Loan Accounts Summary</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Member Name</th>
                <th>Loan Amount</th>
                <th>Issued Date</th>
                <th>Interest/Month</th>
                <th>Monthly Installment</th>
                <th>Total Repayments</th>
                <th>Loan Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM loan_accounts_summary";
        $result = mysqli_query($conn, $sql);
        $count = 1;

        $total_loan = 0;
        $total_interest = 0;
        $total_balance = 0;

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$count}</td>
                    <td>{$row['member_name']}</td>
                    <td>" . number_format($row['loan_amount'], 2) . "</td>
                    <td>{$row['issued_date']}</td>
                    <td>" . number_format($row['interest_per_month'], 2) . "</td>
                    <td>" . number_format($row['monthly_installment'], 2) . "</td>
                    <td>" . number_format($row['total_repayments'], 2) . "</td>
                    <td>" . number_format($row['loan_balance'], 2) . "</td>
                </tr>";

                $total_loan += $row['loan_amount'];
                $total_interest += $row['interest_per_month'];
                $total_balance += $row['loan_balance'];

                $count++;
            }
        } else {
            echo "<tr><td colspan='8'>No data found.</td></tr>";
        }
        ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTALS</td>
                <td><?php echo number_format($total_loan, 2); ?></td>
                <td></td>
                <td><?php echo number_format($total_interest, 2); ?></td>
                <td></td>
                <td></td>
                <td><?php echo number_format($total_balance, 2); ?></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
