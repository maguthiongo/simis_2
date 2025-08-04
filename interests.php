<?php
session_start();

// Allow Admin only
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied.");
}

require_once '../config/db.php';

// Handle interest rate update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_rate = $_POST['interest_rate'];

    $stmt = mysqli_prepare($conn, "UPDATE settings SET value = ? WHERE name = 'interest_rate'");
    mysqli_stmt_bind_param($stmt, "s", $new_rate);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: interests.php");
    exit();
}

// Get current interest rate
$result = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'interest_rate' LIMIT 1");
$current_rate = mysqli_fetch_assoc($result)['value'] ?? 'Not set';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interest Settings - VOV SACCO</title>
    <style>
        body { font-family: Arial; background: #eef3f7; padding: 20px; }
        h2 { color: #004080; }
        .container { background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 500px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; margin-top: 5px; }
        .btn { margin-top: 15px; padding: 10px 15px; background-color: #0074D9; color: white; border: none; border-radius: 4px; }
        a { display: inline-block; margin-top: 15px; color: #0074D9; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Set Interest Rate</h2>
        <p>Current Rate: <strong><?= htmlspecialchars($current_rate) ?>%</strong></p>

        <form method="POST">
            <label>New Interest Rate (%):</label>
            <input type="number" step="0.01" name="interest_rate" required>

            <button class="btn" type="submit">Update Rate</button>
        </form>

        <a href="../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
