<?php
require_once('../config/db.php');

if (isset($_GET['id_number'])) {
    $id = $_GET['id_number'];
    $res = mysqli_query($conn, "SELECT full_name FROM members WHERE id_number = '$id'");
    $row = mysqli_fetch_assoc($res);
    echo $row['full_name'] ?? '';
}
?>
