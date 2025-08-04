<?php
session_start();
require_once('../config/db.php');

$role = strtolower($_SESSION['role'] ?? '');
if (!in_array($role, ['admin', 'staff'])) {
    echo "<h3 style='color: red; text-align: center;'>Access denied. You do not have permission to view this page.</h3>";
    exit;
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$whereClause = '';
if ($start_date && $end_date) {
    $whereClause = "WHERE date BETWEEN '$start_date' AND '$end_date'";
}

// Query filtered data
$sql = "SELECT * FROM expenses $whereClause ORDER BY date DESC";
$result = $conn->query($sql);

// Query grand total (no filter)
$grandResult = $conn->query("SELECT 
    SUM(amount) AS amount, 
    SUM(administrative) AS administrative,
    SUM(board) AS board,
    SUM(infrastructure) AS infrastructure,
    SUM(financial) AS financial,
    SUM(member) AS member,
    SUM(regulatory) AS regulatory,
    SUM(salary) AS salary,
    SUM(other) AS other
FROM expenses");
$grand = $grandResult->fetch_assoc();
$grand['dbal'] = $grand['amount'] - (
    $grand['administrative'] + $grand['board'] + $grand['infrastructure'] +
    $grand['financial'] + $grand['member'] + $grand['regulatory'] +
    $grand['salary'] + $grand['other']
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expenses | VOV SACCO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: greenyellow;
            color: red;
        }
        .container { margin: 20px; }
        .header-buttons { margin-bottom: 15px; }
        .header-buttons button { margin-right: 10px; }
        .filter-form { margin-bottom: 15px; }
        .filter-form input[type="date"] { padding: 5px; margin-right: 10px; }
        .scrollable-table { max-height: 500px; overflow-y: auto; border: 1px solid #ccc; }
        table { border-collapse: collapse; width: 100%; min-width: 1200px; }
        th, td { padding: 8px; border: 1px solid #aaa; text-align: right; }
        th { position: sticky; top: 0; background: #f2f2f2; z-index: 2; }
        tfoot td { background: #f2f2f2; font-weight: bold; }
        td:first-child, th:first-child, tfoot td:first-child,
        td:nth-child(3), th:nth-child(3), tfoot td:nth-child(3) { text-align: left; }
        .back-btn { float: right; }
        .category-cell { text-align: left; white-space: pre-line; }
        .action-buttons button { margin-right: 5px; }
    </style>
    <script>
        function editRow(rowId) {
            const row = document.getElementById('row-' + rowId);
            [...row.querySelectorAll('[data-editable]')].forEach(cell => {
                const value = cell.innerText.trim();
                const input = document.createElement('input');
                input.value = value;
                input.name = cell.dataset.name;
                input.style.width = '100%';
                cell.innerHTML = '';
                cell.appendChild(input);
            });
            row.querySelector('.edit-btn').style.display = 'none';
            row.querySelector('.save-btn').style.display = 'inline-block';
        }

        function saveRow(rowId) {
            const row = document.getElementById('row-' + rowId);
            const formData = new FormData();
            formData.append('id', rowId);
            row.querySelectorAll('input').forEach(input => {
                formData.append(input.name, input.value);
            });

            fetch('update_expense.php', {
                method: 'POST',
                body: formData
            }).then(res => res.text()).then(response => {
                if (response.trim() === 'success') {
                    location.reload();
                } else {
                    alert('Update failed: ' + response);
                }
            });
        }

        function deleteRow(rowId) {
            if (!confirm('Are you sure you want to delete this expense?')) return;
            fetch('delete_expense.php?id=' + rowId)
                .then(res => res.text())
                .then(response => {
                    if (response.trim() === 'success') {
                        location.reload();
                    } else {
                        alert('Delete failed: ' + response);
                    }
                });
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Expense Records</h2>

    <div class="header-buttons">
        <form class="filter-form" method="get">
            <label>Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"></label>
            <label>End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"></label>
            <button type="submit">🔍 Filter</button>
            <button type="button" onclick="window.location.href='expenses.php'">🔄 Clear</button>
        </form>

        <button onclick="window.location.href='add_expense.php'">➕ Add Expense</button>
        <button onclick="window.print()">🖨️ Print</button>
        <button onclick="window.location.href='../dashboard.php'" class="back-btn">⬅️ Back to Dashboard</button>
    </div>

    <div class="scrollable-table">
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Description</th>
                <th>AdministrativeE</th>
                <th>BoardE</th>
                <th>InfrustructureE</th>
                <th>FinancialE</th>
                <th>MemberE</th>
                <th>RegulatoryE</th>
                <th>SalaryE</th>
                <th>OtherE</th>
                <th>DBal</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $totals = [
                'amount' => 0, 'administrative' => 0, 'board' => 0, 'infrastructure' => 0,
                'financial' => 0, 'member' => 0, 'regulatory' => 0, 'salary' => 0, 'other' => 0, 'dbal' => 0
            ];

            while ($row = $result->fetch_assoc()) {
                $admin = $row['administrative'];
                $board = $row['board'];
                $infra = $row['infrastructure'];
                $fin = $row['financial'];
                $mem = $row['member'];
                $reg = $row['regulatory'];
                $sal = $row['salary'];
                $other = $row['other'];
                $amount = $row['amount'];
                $dbal = $amount - ($admin + $board + $infra + $fin + $mem + $reg + $sal + $other);

                $totals['amount'] += $amount;
                $totals['administrative'] += $admin;
                $totals['board'] += $board;
                $totals['infrastructure'] += $infra;
                $totals['financial'] += $fin;
                $totals['member'] += $mem;
                $totals['regulatory'] += $reg;
                $totals['salary'] += $sal;
                $totals['other'] += $other;
                $totals['dbal'] += $dbal;

                echo "<tr id='row-{$row['id']}'>";
                echo "<td data-editable data-name='date'>{$row['date']}</td>";
                echo "<td data-editable data-name='amount'>" . number_format($amount, 2) . "</td>";
                echo "<td data-editable data-name='category' class='category-cell'>" . nl2br(htmlspecialchars(implode("\n", explode(", ", $row['category'])))) . "</td>";
                echo "<td data-editable data-name='description' style='text-align:left'>{$row['description']}</td>";
                echo "<td data-editable data-name='administrative'>" . number_format($admin, 2) . "</td>";
                echo "<td data-editable data-name='board'>" . number_format($board, 2) . "</td>";
                echo "<td data-editable data-name='infrastructure'>" . number_format($infra, 2) . "</td>";
                echo "<td data-editable data-name='financial'>" . number_format($fin, 2) . "</td>";
                echo "<td data-editable data-name='member'>" . number_format($mem, 2) . "</td>";
                echo "<td data-editable data-name='regulatory'>" . number_format($reg, 2) . "</td>";
                echo "<td data-editable data-name='salary'>" . number_format($sal, 2) . "</td>";
                echo "<td data-editable data-name='other'>" . number_format($other, 2) . "</td>";
                echo "<td>" . number_format($dbal, 2) . "</td>";
                echo "<td class='action-buttons'>
                        <button class='edit-btn' onclick='editRow({$row['id']})'>Edit</button>
                        <button class='save-btn' onclick='saveRow({$row['id']})' style='display:none;'>Save</button>
                        <button onclick='deleteRow({$row['id']})'>Delete</button>
                      </td>";
                echo "</tr>";
            }
            ?>
            
</tbody>
      </div>        

            <tfoot>

            <tr>
                <td>Total</td>
                <td><?= number_format($totals['amount'], 2) ?></td>
                <td colspan="2"></td>
                <td><?= number_format($totals['administrative'], 2) ?></td>
                <td><?= number_format($totals['board'], 2) ?></td>
                <td><?= number_format($totals['infrastructure'], 2) ?></td>
                <td><?= number_format($totals['financial'], 2) ?></td>
                <td><?= number_format($totals['member'], 2) ?></td>
                <td><?= number_format($totals['regulatory'], 2) ?></td>
                <td><?= number_format($totals['salary'], 2) ?></td>
                <td><?= number_format($totals['other'], 2) ?></td>
                <td><?= number_format($totals['dbal'], 2) ?></td>
                <td></td>
            </tr>
 
            
           <tfoot>
            <tr>
                <td>G Total</td>
                <td><?= number_format($grand['amount'], 2) ?></td>
                <td colspan="2"></td>
                <td><?= number_format($grand['administrative'], 2) ?></td>
                <td><?= number_format($grand['board'], 2) ?></td>
                <td><?= number_format($grand['infrastructure'], 2) ?></td>
                <td><?= number_format($grand['financial'], 2) ?></td>
                <td><?= number_format($grand['member'], 2) ?></td>
                <td><?= number_format($grand['regulatory'], 2) ?></td>
                <td><?= number_format($grand['salary'], 2) ?></td>
                <td><?= number_format($grand['other'], 2) ?></td>
                <td><?= number_format($grand['dbal'], 2) ?></td>
                <td></td>
            </tr>
            </tfoot>
         
        </table>

</div>
</body>
</html>
