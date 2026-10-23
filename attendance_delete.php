<?php
// attendance_delete.php – Delete a single attendance record and redirect.
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $att_id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM attendance WHERE attendance_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $att_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => '✓ Attendance record deleted.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Error deleting record.'];
    }
    mysqli_stmt_close($stmt);
}

header("Location: attendance_list.php");
exit();
?>
