<?php
session_start();
require_once('../config/db.php');

// Access control: Only Admin or Staff
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    echo "Access denied.";
    exit;
}

// Validate member ID
if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM members WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Member not found.";
    exit;
}

$member = mysqli_fetch_assoc($result);
$successMsg = "";
$errorMsg = "";

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membership_no = $_POST['membership_no'];
    $full_name     = $_POST['full_name'];
    $email         = $_POST['email'];
    $phone         = $_POST['phone'];
    $id_number     = $_POST['id_number'];
    $join_date     = $_POST['join_date'];
    $reg_fee       = is_numeric($_POST['reg_fee']) ? floatval($_POST['reg_fee']) : 0;
    $share_capital = is_numeric($_POST['share_capital']) ? floatval($_POST['share_capital']) : 0;

    // Check for duplicate ID Number (excluding current record)
    $checkQuery = "SELECT * FROM members WHERE id_number = ? AND id != ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "si", $id_number, $id);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $errorMsg = "❌ A member with this ID number already exists.";
    } else {
        // Update with prepared statement
        $update = "UPDATE members SET 
            membership_no = ?, full_name = ?, email = ?, phone = ?, id_number = ?, join_date = ?, reg_fee = ?, share_capital = ?
            WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        // ✅ Correct types: 6 strings, 2 doubles, 1 int = "ssssssddi"
        mysqli_stmt_bind_param($stmt, "ssssssddi", $membership_no, $full_name, $email, $phone, $id_number, $join_date, $reg_fee, $share_capital, $id);


        if (mysqli_stmt_execute($stmt)) {
    // Optional: add a short session message to show success on members.php
    $_SESSION['success'] = "✅ Member updated successfully!";
    header("Location: members.php");
    exit;
} else {
    $errorMsg = "❌ Error updating member: " . mysqli_error($conn);
}



        
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
    <style>
        body { font-family: Arial; padding: 30px; background-color: #ffe6f0; }
        form { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        input, label, button { display: block; width: 100%; margin-bottom: 10px; padding: 8px; font-size: 15px; }
        button { background-color: #d63384; color: white; border: none; cursor: pointer; }
        h2 { text-align: center; color: #d63384; }
        .message { text-align: center; font-weight: bold; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        a { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #d63384; }
    </style>
</head>
<body>

<h2>Edit Member</h2>

<?php if ($successMsg): ?>
    <p class="message success"><?= $successMsg ?></p>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <p class="message error"><?= $errorMsg ?></p>
<?php endif; ?>

<form method="POST">
    <label>Membership Number</label>
    <input type="text" name="membership_no" value="<?= htmlspecialchars($member['membership_no']) ?>" required>

    <label>Full Name</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>">

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>">

    <label>ID Number</label>
    <input type="text" name="id_number" value="<?= htmlspecialchars($member['id_number']) ?>" required>

    <label>Join Date</label>
    <input type="date" name="join_date" value="<?= htmlspecialchars($member['join_date']) ?>" required>

    <label>Registration Fee (RegFee)</label>
    <input type="number" step="0.01" name="reg_fee" value="<?= htmlspecialchars($member['reg_fee']) ?>" required>

    <label>Share Capital (SCapital)</label>
    <input type="number" step="0.01" name="share_capital" value="<?= htmlspecialchars($member['share_capital']) ?>" required>

    <button type="submit">Update Member</button>
</form>

<a href="members.php">← Back to Member List</a>

</body>
</html>
