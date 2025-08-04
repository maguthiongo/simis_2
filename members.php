<?php
session_start();
require_once('../config/db.php');

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    echo "Access denied.";
    exit;
}

$uploadMessage = "";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle); // Skip header

        while (($data = fgetcsv($handle)) !== FALSE) {
            $membership_no = mysqli_real_escape_string($conn, $data[0]);
            $full_name     = mysqli_real_escape_string($conn, $data[1]);
            $email         = mysqli_real_escape_string($conn, $data[2]);
            $phone         = mysqli_real_escape_string($conn, $data[3]);
            $id_number     = mysqli_real_escape_string($conn, $data[4]);
            $join_date     = mysqli_real_escape_string($conn, $data[5]);

            // Skip empty rows
            if (empty($membership_no) || empty($full_name) || empty($id_number)) {
                $errorMessages[] = "⛔ Skipped row with missing membership number, name, or ID.";
                continue;
            }

            // Check for duplicate membership_no or id_number
            $check = mysqli_query($conn, "SELECT id FROM members WHERE membership_no='$membership_no' OR id_number='$id_number'");
            if (mysqli_num_rows($check) > 0) {
                $errorMessages[] = "⚠️ Member '$full_name' skipped - duplicate Membership no or ID.";
                continue;
            }

            // Insert
            $sql = "INSERT INTO members (membership_no, full_name, email, phone, id_number, join_date)
                    VALUES ('$membership_no', '$full_name', '$email', '$phone', '$id_number', '$join_date')";
            if (!mysqli_query($conn, $sql)) {
                $errorMessages[] = "❌ Failed to insert '$full_name': " . mysqli_error($conn);
            }
        }

        fclose($handle);
        if (empty($errorMessages)) {
            $uploadMessage = "✅ CSV file uploaded successfully!";
        } else {
            $uploadMessage = "⚠️ CSV processed with some issues.";
        }
    } else {
        $uploadMessage = "❌ Failed to open CSV file.";
    }
}

// Fetch members
$result = mysqli_query($conn, "SELECT * FROM members ORDER BY membership_no ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Members - VOV SACCO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #ffe6f0; }
        h2 { text-align: center; color: #d63384; }
        .button-bar { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px; gap: 10px; }
        a.button, button.button, input[type="submit"] {
            padding: 8px 12px; background-color: #d63384; color: white; text-decoration: none;
            border-radius: 4px; border: none; cursor: pointer;
        }
        table { border-collapse: collapse; width: 100%; background-color: white; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f8d7da; color: #721c24; }
        .actions a { margin-right: 10px; color: #d63384; }
        .upload-form { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .upload-form input[type="file"] { padding: 5px; }
        .message { margin-top: 10px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
        .info { color: orange; }
        @media print { .no-print { display: none; } }
    </style>
    <script>
        function printTable() {
            window.print();
        }

        function downloadCSV() {
            const table = document.getElementById("membersTable");
            let csv = "";
            for (let row of table.rows) {
                let cols = Array.from(row.cells).map(cell => `"${cell.innerText.trim()}"`);
                csv += cols.join(",") + "\n";
            }
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "members_list.csv";
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</head>
<body>

<h2>VOV SACCO - Member List</h2>

<div class="button-bar no-print">
    <a class="button" href="add_member.php">➕ Add New Member</a>
    <button class="button" onclick="printTable()">🖨️ Print</button>
    <button class="button" onclick="downloadCSV()">⬇️ Download CSV</button>

    <form class="upload-form" method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <input type="submit" value="⬆️ Upload CSV" class="button">
    </form>
    <a class="button" href="../dashboard.php" style="background-color: brown;">🏠 Back to Dashboard</a>

</div>

<?php if ($uploadMessage): ?>
    <p class="message <?= empty($errorMessages) ? 'success' : 'info' ?>"><?= $uploadMessage ?></p>
<?php endif; ?>

<?php if (!empty($errorMessages)): ?>
    <div class="message error">
        <ul>
            <?php foreach ($errorMessages as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<table id="membersTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Membership no</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>National ID</th>

            <th>Join Date</th>
<th>RegFee</th>
<th>SCapital</th>
<th class="no-print">Actions</th>


            <th class="no-print">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $count = 1;
        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($row['membership_no']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['id_number']) ?></td>

                <td><?= htmlspecialchars($row['join_date']) ?></td>
                <td><?= number_format($row['reg_fee'], 2) ?></td>
                <td><?= number_format($row['share_capital'], 2) ?></td>
                <td class="actions no-print">
  
                    <a href="edit_member.php?id=<?= $row['id'] ?>">Edit</a>
                    <a href="delete_member.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="8">No members found.</td></tr>
        <?php endif; ?>
       <?php
// Calculate totals
$totalsQuery = mysqli_query($conn, "SELECT 
    SUM(reg_fee) as total_reg_fee, 
    SUM(share_capital) as total_share_capital 
    FROM members");

$totals = mysqli_fetch_assoc($totalsQuery);
?>
<tr style="font-weight: bold; background-color: #f2f2f2;">
    <td colspan="7" style="text-align: right;">TOTAL</td>
    <td><?= number_format($totals['total_reg_fee'], 2) ?></td>
    <td><?= number_format($totals['total_share_capital'], 2) ?></td>
    <td class="no-print"></td>
</tr>
 
    </tbody>
</table>

</body>
</html>
