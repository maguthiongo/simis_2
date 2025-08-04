<?php
session_start();
require_once('../config/db.php');

// ✅ Normalize role to lowercase for case-insensitive check
$role = strtolower($_SESSION['role'] ?? '');

if (!in_array($role, ['admin', 'staff'])) {
    echo "<h3 style='color: red; text-align: center;'>Access denied. You do not have permission to view this page.</h3>";
    exit;
}

// Fetch expense data
$sql = "SELECT * FROM expenses ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expenses | VOV SACCO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 20px;
        }

        .header-buttons {
            margin-bottom: 15px;
        }

        .header-buttons button {
            margin-right: 10px;
        }

        .scrollable-table {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px;
        }

        th, td {
            padding: 8px;
            border: 1px solid #aaa;
            text-align: right;
        }

        th {
            position: sticky;
            top: 0;
            background: #f2f2f2;
            z-index: 2;
        }

        tfoot td {
            position: sticky;
            bottom: 0;
            background: #f2f2f2;
            font-weight: bold;
        }

        td:first-child, th:first-child, tfoot td:first-child,
        td:nth-child(3), th:nth-child(3), tfoot td:nth-child(3) {
            text-align: left;
        }

        .back-btn {
            float: right;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Expense Records</h2>

    <div class="header-buttons">
        <button onclick="window.location.href='add_expense.php'">➕ Add Expense</button>
        <button onclick="window.print()">🖨️ Print</button>
        <button onclick="window.location.href='dashboard.php'" class="back-btn">⬅️ Back to Dashboard</button>
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
            </tr>
            </thead>
            <tbody>
            <?php
            // Initialize totals
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

                // Accumulate totals
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

                echo "<tr>
                    <td>{$row['date']}</td>

                    <td>" . number_format($amount, 2) . "</td>
                    <td style='text-align:left'>{$row['category']}</td>
                    <td style='text-align:left'>{$row['description']}</td>


                    <td>" . number_format($admin, 2) . "</td>
                    <td>" . number_format($board, 2) . "</td>
                    <td>" . number_format($infra, 2) . "</td>
                    <td>" . number_format($fin, 2) . "</td>
                    <td>" . number_format($mem, 2) . "</td>
                    <td>" . number_format($reg, 2) . "</td>
                    <td>" . number_format($sal, 2) . "</td>
                    <td>" . number_format($other, 2) . "</td>
                    <td>" . number_format($dbal, 2) . "</td>
                </tr>";
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td>Total</td>
                <td><?= number_format($totals['amount'], 2) ?></td>

                <td></td>
                <td></td>
                <td><?= number_format($totals['administrative'], 2) ?></td>
                <td><?= number_format($totals['board'], 2) ?></td>
                <td><?= number_format($totals['infrastructure'], 2) ?></td>
                <td><?= number_format($totals['financial'], 2) ?></td>
                <td><?= number_format($totals['member'], 2) ?></td>
                <td><?= number_format($totals['regulatory'], 2) ?></td>
                <td><?= number_format($totals['salary'], 2) ?></td>
                <td><?= number_format($totals['other'], 2) ?></td>
                <td><?= number_format($totals['dbal'], 2) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
