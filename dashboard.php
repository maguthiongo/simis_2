<?php
session_start();
require_once("config/db.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$_SESSION['login_time'] = $_SESSION['login_time'] ?? date('Y-m-d H:i:s');

// Get summary data
$share_capital = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(share_capital) AS total FROM members"))['total'] ?? 0;

$loan_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(loan_amount) AS total FROM transactions"))['total'] ?? 0;
$savings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(savings) AS total FROM transactions"))['total'] ?? 0;

// $repaid = $principal; // <-- remove if not used
//$repaid = $principal;

$loan_balance_query = "
    SELECT SUM(latest_balance) AS total_balance FROM (
        SELECT t1.loan_balance AS latest_balance
        FROM transactions t1
        INNER JOIN (
            SELECT membership_no, MAX(transaction_date) AS latest_date
            FROM transactions
            GROUP BY membership_no
        ) t2 ON t1.membership_no = t2.membership_no AND t1.transaction_date = t2.latest_date
        GROUP BY t1.membership_no
    ) AS latest_balances
";
$loan_balance = mysqli_fetch_assoc(mysqli_query($conn, $loan_balance_query))['total_balance'] ?? 0;



$interest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(interest_paid) AS total FROM transactions"))['total'] ?? 0;
$expenses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM expenses"))['total'] ?? 0;

$others = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(others) AS total FROM transactions"))['total'] ?? 0;


$members = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM members"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - VOV SACCO</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body {
            background-color: #eef3f7;
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }

.card-pink {
    background-color: #ff69b4; /* HotPink */
    color: white;
}


        .navbar {
            background-color: #004080;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 { margin: 0; font-size: 24px; }
        .user-info { font-size: 14px; }
        .logout {
            background-color: #c0392b;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .dashboard-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .left-panel {
            flex: 1;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .tasks {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .task-btn {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background 0.3s;
        }
        .task-btn:hover { background-color: #0056b3; }

        .right-panel {
            flex: 3;
            min-width: 400px;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease-in-out;
        }
        .card-blue   { background-color: #007bff; color: white; }
        .card-yellow { background-color: #ffc107; color: black; }
        .card-red    { background-color: #ff0000; color: white; }
        .card-green  { background-color: #28a745; color: white; }
        .card-purple { background-color: #6f42c1; color: white; }
        .card-skyblue { background-color: #00bcd4; color: white; }

        .graph-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>VOV SACCO Dashboard</h1>
        <div class="user-info">
            Logged in as <strong><?= htmlspecialchars($username) ?></strong> (<?= $role ?>) <br>
            Login time: <?= $_SESSION['login_time'] ?>
            &nbsp; | &nbsp;
            <a class="logout" href="logout.php">Logout</a>
            <a href="change_password.php" style="color:white; padding-left:10px;">🔒 Change Password</a>

        </div>
    </div>

    <div class="dashboard-container">
        <div class="left-panel">
            <div class="tasks">
                <?php if ($role === 'Admin' || $role === 'Staff'): ?>
                    <a href="pages/members.php" class="task-btn">👥 Manage Members</a>
                    <a href="pages/savings.php" class="task-btn">💰 Manage Savings</a>
                    <a href="pages/loans.php" class="task-btn">💳 Manage Loans</a>
                    <a href="pages/repayments.php" class="task-btn">📄 Manage Repayments</a>
                    <a href="pages/expenses.php" class="task-btn">💼 Manage Expenses</a>
                    <a href="pages/member_statement.php" class="task-btn">📈 Member Statements</a>

                <?php endif; ?>

                <?php if ($role === 'Admin'): ?>
                    <a href="pages/users.php" class="task-btn">🗐 Manage Users</a>
                    <a href="pages/interests.php" class="task-btn">📌 Set Interest Rates</a>
                <?php endif; ?>

                <?php if ($role === 'Member'): ?>
                    <a href="pages/member_statement.php" class="task-btn">📇 View My Statement</a>

                     <a href="pages/loan_application_form.php" class="task-btn">📈 Loan Application Form </a>
                     <a href="pages/downloads.php" class="task-btn">📈 Downloads </a>

                <?php endif; ?>
            </div>
        </div>

        <div class="right-panel">
            <div class="cards">

                <div class="card" style="background-color: saddlebrown; color: white;">
    <h3>Total Share Capital</h3>
    <p><strong>KES <?= number_format($share_capital, 2) ?></strong></p>
</div>


                <div class="card card-pink">
                    <h3>Total Others</h3>
                    <p><strong>KES <?= number_format($others, 2) ?></strong></p>
                </div>
                <div class="card card-blue">
                    <h3>Total Savings</h3>
                    <p><strong>KES <?= number_format($savings, 2) ?></strong></p>
                </div>
                <div class="card card-green">
                    <h3>Total Loan Issued</h3>
                    <p><strong>KES <?= number_format($loan_amount, 2) ?></strong></p>
                </div>

                <div class="card card-yellow">
    <h3>Loan Balance (Remaining)</h3>
    <p><strong>KES <?= number_format($loan_balance, 2) ?></strong></p>
</div>


                <div class="card card-purple">
                    <h3>Interest Collected</h3>
                    <p><strong>KES <?= number_format($interest, 2) ?></strong></p>
                </div>
                <div class="card card-red">
                    <h3>Total Expenses</h3>
                    <p><strong>KES <?= number_format($expenses, 2) ?></strong></p>
                </div>
            </div>

            <div class="graph-section">
                <h3>📊 Financial Overview</h3>
                <div id="bar_chart" style="width: 100%; height: 300px;"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    google.charts.load('current', { 'packages': ['corechart', 'bar'] });
    google.charts.setOnLoadCallback(drawBarChart);

    function drawBarChart() {
        const data = new google.visualization.DataTable();
        data.addColumn('string', 'Metric');
        data.addColumn('number', 'KES');
        data.addColumn({ type: 'string', role: 'style' });
        data.addColumn({ type: 'string', role: 'annotation' });

        data.addRows([


            ['Share Capital', <?= $share_capital ?>, 'color: saddlebrown', 'KES <?= number_format($share_capital) ?>'],
            ['Others', <?= $others ?>, 'color: #ff69b4', 'KES <?= number_format($others) ?>'],
            ['Total Savings', <?= $savings ?>, 'color: #007bff', 'KES <?= number_format($savings) ?>'],
            ['Loan Issued', <?= $loan_amount ?>, 'color: #28a745', 'KES <?= number_format($loan_amount) ?>'],
            ['Loan Balance', <?= $loan_balance ?>, 'color: #ffc107', 'KES <?= number_format($loan_balance) ?>'],
            ['Interest Collected', <?= $interest ?>, 'color: #800080', 'KES <?= number_format($interest) ?>'],
            ['Expenses', <?= $expenses ?>, 'color: #ff0000', 'KES <?= number_format($expenses) ?>']
        ]);

        const options = {
            title: 'SACCO Financial Overview',
            bars: 'horizontal',
            legend: 'none',
            height: 500,
            hAxis: {
                minValue: 0,
                title: 'Amount in KES',
                format: 'short'
            },
            annotations: {
                alwaysOutside: true,
                textStyle: {
                    fontSize: 12,
                    color: '#000',
                    auraColor: 'none'
                }
            },
            bar: { groupWidth: '75%' }
        };

        const chart = new google.visualization.BarChart(document.getElementById('bar_chart'));
        chart.draw(data, options);
    }

    

    </script>
</body>
</html>
