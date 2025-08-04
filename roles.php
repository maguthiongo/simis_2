<?php
session_start();

// Optional: restrict access to logged-in users
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Roles - VOV SACCO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3f7;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #004080;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #004080;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f4f8fb;
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #004080;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h1>VOV SACCO - User Roles & Permissions</h1>

<table>
    <tr>
        <th>Role</th>
        <th>Description</th>
        <th>Permissions</th>
    </tr>
    <tr>
        <td><strong>Admin</strong></td>
        <td>System administrator with full access.</td>
        <td>
            <ul>
                <li>Manage users</li>
                <li>Manage members</li>
                <li>Manage savings, loans, repayments</li>
                <li>Manage expenses and interests</li>
                <li>Full dashboard access</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><strong>Staff</strong></td>
        <td>SACCO employees responsible for transactions.</td>
        <td>
            <ul>
                <li>Manage members</li>
                <li>Handle savings, loans, and repayments</li>
                <li>View dashboard & expenses</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><strong>Member</strong></td>
        <td>Registered SACCO member with limited view access.</td>
        <td>
            <ul>
                <li>View personal savings and loans</li>
                <li>View repayment history</li>
                <li>No access to other members or settings</li>
            </ul>
        </td>
    </tr>
</table>

<a class="back-link" href="dashboard.php">&larr; Back to Dashboard</a>

</body>
</html>
