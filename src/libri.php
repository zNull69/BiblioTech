<?php
require_once 'config.php';

if (!isset($_SESSION['IdUtente'])) {
    header('Location: login.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT codiceFiscale FROM Utente WHERE IdUtente = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['IdUtente']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$utente = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
$etaUtente = calcolaEtaDaCF($utente['codiceFiscale']);

if (isset($_POST['prendiPrestito'])) {
    $idLibro = $_POST['idLibro'];
    
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
          WHERE L.etaTarget <= ?
          GROUP BY L.IdLibro
          ORDER BY L.titolo";


$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $etaUtente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$libri = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Catalogo</title>
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
                <span><h1>BiblioTech - Catalogo</h1></span>
            </a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($messaggio)): ?>
            <div class="messaggio"><?= htmlspecialchars($messaggio) ?></div>
        <?php endif; ?>
        
        <div class="libri-grid">
            <?php foreach ($libri as $libro): ?>
                <div class="card h-100 card-bibliotech shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary"><?= htmlspecialchars($libro['genere']) ?></span>
                    <small class="text-muted">Età: <?= $libro['etaTarget'] ?>+</small>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?= htmlspecialchars($libro['titolo']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($libro['autore']) ?></h6>
                </div>
                <div class="card-footer bg-white border-top-0 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <?php if ($libro['copie_disponibili'] > 0): ?>
                            <span class="fw-bold text-success"><?= $libro['copie_disponibili'] ?> / <?= $libro['copie_totali'] ?> Copie disponibili</span>
                        <?php else: ?>
                            <span class="fw-bold text-danger">Nessuna copia disponibile</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button onclick="location.href='libro.php?id=<?= $libro['IdLibro'] ?>'"
                                class="btn btn-sm btn-dettagli w-50">
                            Dettagli
                        </button>
                        <form method="POST" class="w-50">
                            <input type="hidden" name="idLibro" value="<?= $libro['IdLibro'] ?>">
                            <button type="submit" name="prendiPrestito"
                                    class="btn btn-sm btn-bibliotech w-100"
                                    <?= $libro['copie_disponibili'] == 0 ? 'disabled' : '' ?>>
                                Prenota
                            </button>
                        </form>
                    </div>
                </div>
            </div>
                
            <?php endforeach; ?>
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
