<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];
$membership_no = $_GET['membership_no'] ?? '';

// Fetch members for dropdown
function fetchMembers($conn) {
    $result = $conn->query("SELECT membership_no, full_name, id_number FROM members ORDER BY membership_no ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}



// Fetch selected member details
function fetchMemberDetails($conn, $membership_no) {
    $stmt = $conn->prepare("SELECT full_name, id_number FROM members WHERE membership_no = ?");
    $stmt->bind_param("s", $membership_no);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch transactions for the member (ordered by latest first)

function fetchTransactions($conn, $membership_no, $start_date = null, $end_date = null) {
    if ($start_date && $end_date) {

        $query = "SELECT * FROM transactions 
                  WHERE membership_no = ? 
                  AND transaction_date BETWEEN ? AND ? 
                  ORDER BY transaction_date ASC, id ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $membership_no, $start_date, $end_date);
    } else {
        $query = "SELECT * FROM transactions 
                  WHERE membership_no = ? 
                  ORDER BY transaction_date DESC, id DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $membership_no);
    }

    $stmt->execute();
    return $stmt->get_result();
}


$members = fetchMembers($conn);
$member = fetchMemberDetails($conn, $membership_no);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$transactions = $membership_no ? fetchTransactions($conn, $membership_no, $start_date, $end_date) : [];
?>

<!DOCTYPE html>
<html>
<style>
@media print {
    body {
        font-size: 10px;
    }

    table {
        font-size: 10px;
        width: 100%;
        table-layout: fixed;
    }

    th, td {
        padding: 4px;
        word-wrap: break-word;
    }

    .controls,
    .btn,
    .btn-back {
        display: none !important;
    }

    @page {
        size: A4 landscape;
        margin: 10mm;
    }
}


.btn-back {
    background-color: #007bff;
    color: white;
    padding: 8px 14px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 15px;
}
.btn-back:hover {
    background-color: #0056b3;
}

.member-info {
    text-align: center;
    margin-bottom: 20px;
    font-size: 18px;
    line-height: 1.6;
    background-color: goldenrod; /* 🔶 goldenrod background */
    padding: 15px;
    border-radius: 8px;
}

    body { font-family: Arial; margin: 20px; }
    .table-wrapper {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }
    thead th {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
        z-index: 2;
    }
    tfoot td {
        position: sticky;
        bottom: 0;
        background-color: #d7f0e3;
        font-weight: bold;
        z-index: 1;
    }
    .btn {
        padding: 6px 12px;
        border: none;
        background: #28a745;
        color: #fff;
        cursor: pointer;
        border-radius: 4px;
    }
    .btn:hover { background: #218838; }
    .btn-danger { background: #dc3545; }
    .btn-danger:hover { background: #c82333; }
    .controls { margin: 10px 0; }
</style>




<head>
    <title>Member Statement</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .btn { padding: 6px 12px; border: none; background: #28a745; color: #fff; cursor: pointer; border-radius: 4px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .controls { margin: 10px 0; }
        .totals { font-weight: bold; 
        .table-wrapper {
    height: 500px; /* Adjust height as needed */
    overflow-y: scroll;
    border: 1px solid #ccc;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    position: sticky;
    top: 0;
    background-color: #f2f2f2;
    z-index: 2;
}

tfoot td {
    position: sticky;
    bottom: 0;
    background-color: #e0f7fa;
    font-weight: bold;
    z-index: 1;
}


        }
    </style>
    <script>
        function onMemberChange(select) {
            const url = 'member_statement.php?membership_no=' + select.value;
            window.location.href = url;
        }
        function openAddModal() {
            const url = 'add_transaction.php?membership_no=<?= $membership_no ?>';
            window.open(url, 'Add Transaction', 'width=700,height=600');
        }
        function openEditModal(id) {
            const url = 'edit_transaction.php?id=' + id;
            window.open(url, 'Edit Transaction', 'width=700,height=600');
        }
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this transaction?")) {
                window.location.href = 'delete_transaction.php?id=' + id;
            }
        }
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
<div class="member-info">
<h2>Member Statement</h2> 



<form method="get">
    <label>Select Member:</label>
    <select name="membership_no" onchange="onMemberChange(this)">
        <option value="">--Select Member--</option>
        <?php foreach ($members as $m): ?>
            <option value="<?= $m['membership_no'] ?>" <?= ($membership_no == $m['membership_no']) ? 'selected' : '' ?>>
                <?= $m['membership_no'] ?> - <?= $m['full_name'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($membership_no): ?>


    <p><strong>Membership No:</strong> <?= htmlspecialchars($membership_no) ?>
    <strong>Name:</strong> <?= htmlspecialchars($member['full_name'] ?? 'N/A') ?>
    <strong>ID No:</strong> <?= htmlspecialchars($member['id_number'] ?? 'N/A') ?></p>
    <p><strong>Report Date:</strong> <?= date('Y-m-d') ?>   <a href="../dashboard.php" class="btn btn-back">← Back to Dashboard</a> </p>


    <div class="controls">
        <form method="get">
            <input type="hidden" name="membership_no" value="<?= htmlspecialchars($membership_no) ?>">
            Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <button class="btn" type="submit">Filter</button>
            <button class="btn" type="button" onclick="printPage()">Print</button>


            <button class="btn" type="button" onclick="window.location='export_statement.php?membership_no=<?= $membership_no ?>&format=csv'">Export CSV</button>



            <button class="btn" onclick="window.location='export_statement.php?membership_no=<?= $membership_no ?>&format=pdf'">Export PDF</button>
            <?php if ($role == 'Admin' || $role == 'Staff'): ?>
                <button class="btn" type="button" onclick="openAddModal()">Add Transaction</button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($role == 'Admin' || $role == 'Staff'): ?>
        <form method="post" enctype="multipart/form-data" action="upload_transactions.php">
       <input type="hidden" name="membership_no" value="<?= htmlspecialchars($membership_no) ?>">
 </div>
        <label>Upload CSV:</label>
       <input type="file" name="upload_file" accept=".csv" required>
       <button class="btn" type="submit">Upload</button>


        </form>


    <?php endif; ?>

    <div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Description</th>
                <th>Deposit</th>
                <th>Savings</th>
                <th>Cumulative Savings</th>
                <th>Christmas Fund</th>
                <th>Issued Loan</th>
                <th>Loan Principal Paid</th>
                <th>Interest Paid</th>
                <th>Loan Balance</th>
                <?php if ($role == 'Admin' || $role == 'Staff'): ?>
                    <th>Others</th>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
    <tr>
        <th style="width: 30px;">ID</th>
        <th style="width: 70px;">Date</th>
        <th style="width: 120px;">Description</th>
        <th style="width: 70px;">Deposit</th>
        <th style="width: 70px;">Savings</th>
        <th style="width: 90px;">Cumulative Savings</th>
        <th style="width: 70px;">Christmas</th>
        <th style="width: 70px;">Loan</th>
        <th style="width: 90px;">Principal</th>
        <th style="width: 90px;">Interest</th>
        <th style="width: 90px;">Balance</th>
        <?php if ($role == 'Admin' || $role == 'Staff'): ?>
             <th style="width: 90px;">Others</th>
            <th style="width: 90px;">Actions</th>
        <?php endif; ?>
    </tr>

        </thead>
        <tbody>


            <?php
$cumulative_savings = 0;
$totals = ['deposit'=>0,'loan'=>0,'loan_balance'=>0,'principal'=>0,'interest'=>0,'savings'=>0,'christmas'=>0,'others'=>0];

$cumulative_savings = 0;
$display_rows = [];

while ($row = $transactions->fetch_assoc()) {
    $cumulative_savings += $row['savings'] ?? 0;
    $row['cumulative_savings'] = $cumulative_savings;
    $display_rows[] = $row;
}


// Step 3: Loop through display-ready rows
foreach ($display_rows as $row):
    $totals['deposit'] += $row['amount_deposited'] ?? 0;
    $totals['loan'] += $row['loan_amount'] ?? 0;
    $totals['loan_balance'] += $row['loan_balance'] ?? 0;
    $totals['principal'] += $row['loan_principal'] ?? 0;
    $totals['interest'] += $row['interest_paid'] ?? 0;
    $totals['savings'] += $row['savings'] ?? 0;
    $totals['christmas'] += $row['christmas_fund'] ?? 0;
    $totals['others'] += $row['others'] ?? 0;
?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['transaction_date']) ?></td>
    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
    <td><?= number_format($row['amount_deposited'] ?? 0, 2) ?></td>
    <td><?= number_format($row['savings'] ?? 0, 2) ?></td>
    <td><?= number_format($row['cumulative_savings'], 2) ?></td>
    <td><?= number_format($row['christmas_fund'] ?? 0, 2) ?></td>
    <td><?= number_format($row['loan_amount'] ?? 0, 2) ?></td>
    <td><?= number_format($row['loan_principal'] ?? 0, 2) ?></td>
    <td><?= number_format($row['interest_paid'] ?? 0, 2) ?></td>
    <td><?= number_format($row['loan_balance'] ?? 0, 2) ?></td>
    <td><?= number_format($row['others'] ?? 0, 2) ?></td>

    <?php if ($role == 'Admin' || $role == 'Staff'): ?>
    <td>
        <button class="btn" onclick="openEditModal(<?= $row['id'] ?>)">Edit</button>
        <button class="btn btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; 


$most_recent_balance = end($display_rows)['loan_balance'] ?? 0;

?>
<?php
$most_recent_balance = end($display_rows)['loan_balance'] ?? 0;

?>

        </tbody>


        <tfoot>
            <tr class="totals">
                <td colspan="3">Totals</td>
                <td><?= number_format($totals['deposit'], 2) ?></td>
                <td><?= number_format($totals['savings'], 2) ?></td>
                <td><?= number_format($cumulative_savings, 2) ?></td>
                <td><?= number_format($totals['christmas'], 2) ?></td>
                <td><?= number_format($totals['loan'], 2) ?></td>
                <td><?= number_format($totals['principal'], 2) ?></td>
                <td><?= number_format($totals['interest'], 2) ?></td>

               <td><?= number_format($most_recent_balance, 2) ?></td> <!-- ✅ Updated this -->

                <td><?= number_format($totals['others'], 2) ?></td>

                <?php if ($role == 'Admin' || $role == 'Staff'): ?><td></td><?php endif; ?>
            </tr>
        </tfoot>
    </table>
    </div>

<?php else: ?>
    <p>Please select a member to view the statement.</p>
<?php endif; ?>

</body>
</html>
