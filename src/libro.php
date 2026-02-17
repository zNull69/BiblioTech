<?php
require_once 'config.php';

if (!isset($_SESSION['IdUtente'])) {
    header('Location: login.php');
    exit;
}

$idLibro = $_GET['id'] ?? 0;

$stmt = mysqli_prepare($conn, "SELECT codiceFiscale FROM Utente WHERE IdUtente = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['IdUtente']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$utente = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
$etaUtente = calcolaEtaDaCF($utente['codiceFiscale']);

if (isset($_POST['prendiPrestito'])) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as numPrestiti FROM Prestito WHERE IdUtente = ? AND dataRestituzione IS NULL");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['IdUtente']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $numPrestiti = $row['numPrestiti'];
    mysqli_stmt_close($stmt);
    
    if ($numPrestiti >= 3) {
        $messaggio = "Hai raggiunto il limite di 3 prestiti contemporanei";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT IdCopia FROM Copia WHERE IdLibro = ? AND stato = 'disponibile' LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idLibro);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $copia = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($copia) {
            $dataScadenza = date('Y-m-d', strtotime('+30 days'));
            
            mysqli_begin_transaction($conn);
            
            $stmt = mysqli_prepare($conn, "UPDATE Copia SET stato = 'prestito' WHERE IdCopia = ?");
            mysqli_stmt_bind_param($stmt, "i", $copia['IdCopia']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO Prestito (IdCopia, IdUtente, dataPrestito, dataScadenza) VALUES (?, ?, CURDATE(), ?)");
            mysqli_stmt_bind_param($stmt, "iis", $copia['IdCopia'], $_SESSION['IdUtente'], $dataScadenza);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            mysqli_commit($conn);
            
            $messaggio = "Prestito effettuato con successo!";
        } else {
            $messaggio = "Nessuna copia disponibile";
        }
    }
}

$query = "SELECT L.IdLibro, L.titolo, L.autore, L.genere, L.etaTarget,
          COUNT(CASE WHEN C.stato = 'disponibile' THEN 1 END) as copie_disponibili,
          COUNT(C.IdCopia) as copie_totali
          FROM Libro L
          LEFT JOIN Copia C ON L.IdLibro = C.IdLibro
          WHERE L.IdLibro = ?
          GROUP BY L.IdLibro";


$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $idLibro);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$libro = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$libro) {
    header('Location: libri.php');
    exit;
}

$accessoNegato = $libro['etaTarget'] > $etaUtente;


?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - <?= htmlspecialchars($libro['titolo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="static/CSS/index.css">
    <link rel="shortcut icon" href="static/CSS/imgs/logo.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-bibliotech sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="40" class="me-2 bg-light rounded px-1" />
                <span>BiblioTech</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3 border border-light rounded"><a class="nav-link" href="prestiti.php">I Miei Prestiti</a></li>
                    <li class="nav-item me-3 border border-light rounded"><a class="nav-link" href="libri.php">Catalogo</a></li>
                    <?php if (getRuolo($conn, $_SESSION['IdUtente']) === 'admin'): ?>
                        <li class="nav-item me-3 border border-light rounded"><a class="nav-link" href="gestione_restituzioni.php">Dashboard Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item me-3 border border-danger rounded"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="header">
        <div class="header-content">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="200" class="rounded px-1" />
                <span><h1>BiblioTech</h1></span>
            </a>
        </div>
    </div>
    
    <div class="container py-4">
        <?php if (isset($messaggio)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($messaggio) ?></div>
        <?php endif; ?>

        <?php if ($accessoNegato): ?>
            <div class="alert alert-warning">
                Questo libro è destinato a lettori di età pari o superiore a <?= $libro['etaTarget'] ?> anni.
                Non puoi prenderlo in prestito.
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0 fw-bold"><?= htmlspecialchars($libro['titolo']) ?></h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-4 text-muted" style="width: 30%;">Autore</th>
                            <td><?= htmlspecialchars($libro['autore']) ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 text-muted">Genere</th>
                            <td><?= htmlspecialchars($libro['genere']) ?></td>
                        </tr>
                        <tr>
                            <th class="ps-4 text-muted">Età Target</th>
                            <td><?= $libro['etaTarget'] ?>+ anni</td>
                        </tr>
                        <tr>
                            <th class="ps-4 text-muted">Disponibilità</th>
                            <td>
                                <?php if ($libro['copie_disponibili'] > 0): ?>
                                    <span class="text-success fw-bold"><?= $libro['copie_disponibili'] ?></span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">0</span>
                                <?php endif; ?>
                                su <?= $libro['copie_totali'] ?> copie totali
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="btn-container">
            <?php if (!$accessoNegato): ?>
            <form method="POST">
                <button type="submit" name="prendiPrestito" class="btn-bibliotech"
                        <?= $libro['copie_disponibili'] == 0 ? 'disabled' : '' ?>>
                    Richiedi prestito
                </button>
            </form>
            <?php endif; ?>
            <button onclick="location.href='libri.php'" class="btn-dettagli mt-2">
                Torna al Catalogo
            </button>
        </div>
    </div>
    <footer class="footer-bibliotech">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="80" class="rounded px-1" />
            <span><h3>BiblioTech </h3></span>
        </a>
        <p>Un progetto di Francesco Tenerelli - V ITIA A - &copy tutti i diritti riservati</p>
    </footer>
</body>
</html>
