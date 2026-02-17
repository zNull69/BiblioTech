<?php
require_once 'config.php';

if (isset($_SESSION['IdUtente']) && isset($_SESSION['tokenSessione'])) {
    $stmt = mysqli_prepare($conn, "DELETE FROM Sessione WHERE IdUtente = ? AND tokenSessione = ?");
    mysqli_stmt_bind_param($stmt, "is", $_SESSION['IdUtente'], $_SESSION['tokenSessione']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

session_unset();
session_destroy();

header('Location: login.php');
exit;
?>
