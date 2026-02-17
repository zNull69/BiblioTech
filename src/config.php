<?php
session_start();

$host = 'db';
$dbname = 'bibliotech_db';
$username = 'bibliotech_user';
$password = 'BilioT3ch';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Errore di connessione: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function getRuolo($conn, $idUtente) {
    $stmt = mysqli_prepare($conn, "SELECT ruolo FROM Utente WHERE IdUtente = ?");
    mysqli_stmt_bind_param($stmt, "i", $idUtente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row ? $row['ruolo'] : null;
}

function calcolaEtaDaCF($codiceFiscale) {
    $anno = substr($codiceFiscale, 6, 2);
    $anno = ($anno > date('y')) ? '19' . $anno : '20' . $anno;
    $mese = substr($codiceFiscale, 8, 1);
    $mesi = ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'H'=>6,'L'=>7,'M'=>8,'P'=>9,'R'=>10,'S'=>11,'T'=>12];
    $mese = $mesi[$mese];
    $giorno = intval(substr($codiceFiscale, 9, 2));
    if ($giorno > 40) $giorno -= 40;
    
    $dataNascita = new DateTime("$anno-$mese-$giorno");
    $oggi = new DateTime();
    return $oggi->diff($dataNascita)->y;
}
?>
