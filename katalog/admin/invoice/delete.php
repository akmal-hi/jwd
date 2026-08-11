<?php
require_once '../../config.php';

/** @var mysqli $conn */
global $conn;

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM invoices WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['success'] = 'Invoice berhasil dihapus!';
} else {
    $_SESSION['error'] = 'ID invoice tidak valid!';
}

header('Location: invoice.php');
exit;