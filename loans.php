<?php
session_start();
require_once('../config/db.php');

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    echo "Access denied.";
    exit;
}

// Handle filters
$report_date = $_GET['report_date'] ?? date('Y-m-d');
$filter_status = $_GET['filter_status'] ?? '';

// SQL query with optional filter
$where = '';
if (!empty($filter_status)) {
    $filter_status_safe = mysqli_real_escape_string($conn, $filter_status);
    $where = "WHERE loans.loan_status = '$filter_status_safe'";
}

$sql = "SELECT loans.*, members.full_name 
        FROM loans 
        LEFT JOIN members ON loans.membership_no = members.membership_no
        $where
        ORDER BY loans.issued_date ASC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error fetching loans: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loans - VOV SACCO</title>
    <style>
        body { font-family: Arial; padding: 20px; background-color: #f8f9fa; }
        h2 { text-align: center; color: #343a40; }

        a.button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .form-inline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .back-dashboard {
    background-color: red;
    margin-left: 10px;
        }
        .back-dashboard:hover {
      background-color: darkred;
    }


        .scrollable-container {
            overflow: auto;
            max-height: 600px;
            border: 1px solid #ccc;
            background: white;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            white-space: nowrap;
        }

        thead th {
            position: sticky;
            top: 0;
            background: #f2f2f2;
            z-index: 3;
        }

        /* Sticky 2nd column (Loan No) */
        th:nth-child(2), td:nth-child(2) {
            position: sticky;
            left: 50px;
            background: #f9f9f9;
            z-index: 2;
        }

        /* Sticky last column (Actions) */
        th:last-child, td:last-child {
            position: sticky;
            right: 0;
            background: #f9f9f9;
            z-index: 2;
        }

        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }

        /* Status color codes */
        .status-active { background-color: #008000; }
        .status-poor-repayment { background-color: #FFFF00; }
        .status-defaulted { background-color: #ff0000; }
        .status-cleared { background-color: #87ceeb; }
        .status-others { background-color: #e2e3e5; }
    </style>
</head>
<body>

<h2>VOV SACCO - Loan Accounts</h2>

<!-- Filter by status -->
<form method="get" class="form-inline">
    <label><strong>Filter by Status:</strong></label>
    <select name="filter_status" onchange="this.form.submit()">
        <option value="">-- All --</option>
        <?php
        $statuses = ['Active', 'Poor repayment', 'Defaulted', 'Cleared', 'Others'];
        foreach ($statuses as $s) {
            $selected = ($filter_status === $s) ? 'selected' : '';
            echo "<option value=\"$s\" $selected>$s</option>";
        }
        ?>
    </select>
</form>

<!-- Report date form -->
<form method="get" class="form-inline">
    <label for="report_date"><strong>Report Date:</strong></label>
    <input type="date" id="report_date" name="report_date" value="<?= $report_date ?>">
    <input type="hidden" name="filter_status" value="<?= $filter_status ?>">
    <button type="submit">Update</button>
</form>

<p><strong>Date of Report:</strong> <?= $report_date ?></p>

<!-- Action buttons -->
<a class="button" href="add_loan.php">➕ Add New Loan</a>
<a class="button" href="#" onclick="window.print(); return false;">🖨️ Print</a>
<a class="button" href="#" onclick="downloadCSV(); return false;">⬇️ Download CSV</a>
<a class="button back-dashboard" href="../dashboard.php">← Back to Dashboard</a>

<div class="scrollable-container">
    <table id="loanTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Loan No</th>
                <th>Membership No</th>
                <th>Member Name</th>
                <th>Loan Amount</th>
                <th>Issued Date</th>
                <th>Monthly Installment</th>
                <th>Interest (Monthly)</th>
                <th>Loan Balance</th>
                <th>Installment(s)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 1;
        $total_amount = $total_installment = $total_interest = $total_balance = 0;

        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):

                if ($row['loan_balance'] == 0 && $row['loan_status'] != 'Cleared') {
                    mysqli_query($conn, "UPDATE loans SET loan_status = 'Cleared' WHERE id = {$row['id']}");
                    $row['loan_status'] = 'Cleared';
                }

                $total_amount += $row['loan_amount'];
                $total_installment += $row['monthly_installment'];
                $total_interest += $row['monthly_interest'];
                $total_balance += $row['loan_balance'];

                $status_class = 'status-' . strtolower(str_replace(' ', '-', $row['loan_status']));
        ?>
            <tr class="<?= $status_class ?>">
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($row['loan_no']) ?></td>
                <td><?= htmlspecialchars($row['membership_no']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= number_format($row['loan_amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['issued_date']) ?></td>
                <td><?= number_format($row['monthly_installment'], 2) ?></td>
                <td><?= number_format($row['monthly_interest'], 2) ?></td>
                <td><?= number_format($row['loan_balance'], 2) ?></td>
                <td><?= (int)$row['no_of_installments'] ?></td>
                <td>
                    <select onchange="updateStatus(<?= $row['id'] ?>, this.value)">
                        <?php foreach ($statuses as $status): ?>
                            <option <?= $row['loan_status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="actions">
                    <a href="edit_loan.php?id=<?= $row['id'] ?>">Edit</a>
                    <a href="delete_loan.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="12">No loan records found.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f1f1f1;">
                <td colspan="4">TOTALS</td>
                <td><?= number_format($total_amount, 2) ?></td>
                <td></td>
                <td><?= number_format($total_installment, 2) ?></td>
                <td><?= number_format($total_interest, 2) ?></td>
                <td><?= number_format($total_balance, 2) ?></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
function updateStatus(loanId, newStatus) {
    fetch('update_loan_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${loanId}&status=${encodeURIComponent(newStatus)}`
    })
    .then(res => res.text())
    .then(msg => console.log(msg))
    .catch(err => console.error('Error:', err));
}

function downloadCSV() {
    const table = document.getElementById("loanTable");
    let csv = [];
    for (let row of table.rows) {
        let rowData = [];
        for (let cell of row.cells) {
            rowData.push(`"${cell.innerText}"`);
        }
        csv.push(rowData.join(","));
    }

    let blob = new Blob([csv.join("\\n")], { type: "text/csv" });
    let a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "loans_report_<?= date('Ymd') ?>.csv";
    a.click();
}
</script>

</body>
</html>
