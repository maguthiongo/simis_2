<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];

// Set membership_no based on role
if ($role === 'Member') {
    $membership_no = $_SESSION['membership_no'] ?? '';
} else {
    $membership_no = $_GET['membership_no'] ?? '';
}

// Handle email send request
if (isset($_GET['email_statement']) && $membership_no) {
    $stmt = $conn->prepare("SELECT email, full_name FROM members WHERE membership_no = ?");
    $stmt->bind_param("s", $membership_no);
    $stmt->execute();
    $member_data = $stmt->get_result()->fetch_assoc();

    $email_to = $member_data['email'] ?? '';
    $full_name = $member_data['full_name'] ?? 'Member';
    $sacco_email = "voiceofvisionsacco@gmail.com";

    $subject = "VOV SACCO Statement";
    $message = "Dear $full_name,\n\nPlease find your VOV SACCO statement attached.\n\nThank you.\nVoice of Vision SACCO";
    $headers = "From: $sacco_email\r\nReply-To: $sacco_email\r\nContent-Type: text/plain; charset=UTF-8\r\n";

    $success = mail($email_to, $subject, $message, $headers) && mail($sacco_email, $subject, $message, $headers);
    echo "<script>alert('" . ($success ? "Statement emailed successfully." : "Failed to send email.") . "');</script>";
}

function fetchMembers($conn) {
    $result = $conn->query("SELECT membership_no, full_name, id_number FROM members ORDER BY membership_no ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchMemberDetails($conn, $membership_no) {
    $stmt = $conn->prepare("SELECT full_name, id_number FROM members WHERE membership_no = ?");
    $stmt->bind_param("s", $membership_no);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function fetchTransactions($conn, $membership_no, $start_date = null, $end_date = null) {
    if ($start_date && $end_date) {
        $query = "SELECT * FROM transactions WHERE membership_no = ? AND transaction_date BETWEEN ? AND ? ORDER BY transaction_date ASC, id ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $membership_no, $start_date, $end_date);
    } else {
        $query = "SELECT * FROM transactions WHERE membership_no = ? ORDER BY transaction_date ASC, id ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $membership_no);
    }

    $stmt->execute();
    return $stmt->get_result();
}

$members = ($role !== 'Member') ? fetchMembers($conn) : [];
$member = $membership_no ? fetchMemberDetails($conn, $membership_no) : null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$transactions = $membership_no ? fetchTransactions($conn, $membership_no, $start_date, $end_date) : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Statement</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .btn { padding: 6px 12px; border: none; background: #28a745; color: #fff; cursor: pointer; border-radius: 4px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-back { background: #007bff; color: white; padding: 8px 14px; border-radius: 4px; text-decoration: none; margin-bottom: 15px; }
        .member-info { background-color: goldenrod; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        thead th { position: sticky; top: 0; background-color: #f2f2f2; }
        tfoot td { position: sticky; bottom: 0; background-color: #e0f7fa; font-weight: bold; }
        @media print {
            .controls, .btn, .btn-back { display: none !important; }
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['msg'])): ?>
    <p style="color: green; font-weight: bold;"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>


<div class="member-info">
    <h2>Member Statement</h2>
    <?php if ($role !== 'Member'): ?>
        <form method="get">
            <label>Select Member:</label>
            <select name="membership_no" onchange="this.form.submit()">
                <option value="">--Select Member--</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?= $m['membership_no'] ?>" <?= ($membership_no == $m['membership_no']) ? 'selected' : '' ?>>
                        <?= $m['membership_no'] ?> - <?= $m['full_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php else: ?>
        <p><strong>Welcome, Member. Here is your statement:</strong></p>
    <?php endif; ?>
</div>

<?php if ($membership_no): ?>
    <p><strong>Membership No:</strong> <?= htmlspecialchars($membership_no) ?>
       <strong>Name:</strong> <?= htmlspecialchars($member['full_name'] ?? 'N/A') ?>
       <strong>ID No:</strong> <?= htmlspecialchars($member['id_number'] ?? 'N/A') ?></p>
    <p><strong>Report Date:</strong> <?= date('Y-m-d') ?> <a href="../dashboard.php" class="btn btn-back">&larr; Back</a></p>

    <div class="controls">
        <form method="get">
            <input type="hidden" name="membership_no" value="<?= htmlspecialchars($membership_no) ?>">
            Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <button class="btn" type="submit">Filter</button>
            <button class="btn" type="button" onclick="window.print()">Print</button>

<a class="btn" href="add_transaction.php?membership_no=<?= $membership_no ?>">Add Transaction</a>
</button>

            <button class="btn" type="button" onclick="window.location='export_statement.php?membership_no=<?= $membership_no ?>&format=csv'">Export CSV</button>
            <button class="btn" type="button" onclick="window.location='export_statement.php?membership_no=<?= $membership_no ?>&format=pdf'">Export PDF</button>

            <button class="btn" type="button" onclick="window.location='?membership_no=<?= $membership_no ?>&email_statement=true'">Email Statement</button>

           

        </form>

<form method="POST" action="upload_transactions.php" enctype="multipart/form-data" style="display:inline;">
    <input type="hidden" name="membership_no" value="<?= htmlspecialchars($membership_no) ?>">
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit" class="btn">Upload CSV</button>
</form>


    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Date</th><th>Description</th><th>Deposit</th><th>Savings</th><th>Cumulative</th><th>Christmas</th>
                <th>Loan Issued</th><th>Principal</th><th>Interest</th><th>Balance</th>
                <?php if ($role !== 'Member'): ?><th>Others</th><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $totals = ['deposit'=>0,'loan'=>0,'loan_balance'=>0,'principal'=>0,'interest'=>0,'savings'=>0,'christmas'=>0,'others'=>0];
            $cumulative_savings = 0;
            $rows = [];

            while ($row = $transactions->fetch_assoc()) {
                $cumulative_savings += $row['savings'] ?? 0;
                $row['cumulative_savings'] = $cumulative_savings;
                $rows[] = $row;
            }

            foreach ($rows as $row):
                foreach ($totals as $key => &$total) {
                    $total += $row[$key] ?? 0;
                }
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['transaction_date'] ?></td>
                <td><?= $row['description'] ?></td>
                <td><?= number_format($row['amount_deposited'] ?? 0, 2) ?></td>
                <td><?= number_format($row['savings'] ?? 0, 2) ?></td>
                <td><?= number_format($row['cumulative_savings'], 2) ?></td>
                <td><?= number_format($row['christmas_fund'] ?? 0, 2) ?></td>
                <td><?= number_format($row['loan_amount'] ?? 0, 2) ?></td>
                <td><?= number_format($row['loan_principal'] ?? 0, 2) ?></td>
                <td><?= number_format($row['interest_paid'] ?? 0, 2) ?></td>
                <td><?= number_format($row['loan_balance'] ?? 0, 2) ?></td>
                <?php if ($role !== 'Member'): ?>
                    <td><?= number_format($row['others'] ?? 0, 2) ?></td>
                    <td>
                        <button class="btn" onclick="window.open('edit_transaction.php?id=<?= $row['id'] ?>','Edit','width=600,height=500')">Edit</button>
                        <button class="btn btn-danger" onclick="if(confirm('Delete?')) window.location='delete_transaction.php?id=<?= $row['id'] ?>'">Delete</button>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totals</td>
                <td><?= number_format($totals['deposit'], 2) ?></td>
                <td><?= number_format($totals['savings'], 2) ?></td>
                <td><?= number_format($cumulative_savings, 2) ?></td>
                <td><?= number_format($totals['christmas'], 2) ?></td>
                <td><?= number_format($totals['loan'], 2) ?></td>
                <td><?= number_format($totals['principal'], 2) ?></td>
                <td><?= number_format($totals['interest'], 2) ?></td>
                <td><?= number_format($rows[count($rows)-1]['loan_balance'] ?? 0, 2) ?></td>
                <?php if ($role !== 'Member'): ?><td><?= number_format($totals['others'], 2) ?></td><td></td><?php endif; ?>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <p>Please select a member to view the statement.</p>
<?php endif; ?>
</body>
</html>
