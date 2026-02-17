<?php
require_once 'config.php';

if (!isset($_SESSION['IdUtente'])) {
    header('Location: login.php');
    exit;
}

$query = "SELECT P.IdPrestito, L.titolo, L.autore, P.dataPrestito, P.dataScadenza, C.IdCopia
          FROM Prestito P
          INNER JOIN Copia C ON P.IdCopia = C.IdCopia
          INNER JOIN Libro L ON C.IdLibro = L.IdLibro
          WHERE P.IdUtente = ? AND P.dataRestituzione IS NULL
          ORDER BY P.dataPrestito DESC";


$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['IdUtente']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$prestiti = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$numPrestiti = count($prestiti);
$puoFarePrestiti = $numPrestiti < 3;



?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - I Miei Prestiti</title>
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
                <span><h1>BiblioTech - I miei prestiti</h1></span>
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="info-box">
            <strong>Prestiti attivi:</strong> <?= $numPrestiti ?> / 3 massimi consentiti
            <?php if (!$puoFarePrestiti): ?>
                <br><span style="color: #721c24; font-weight: bold; margin-top: 10px; display: inline-block;">Hai raggiunto il limite massimo di prestiti</span>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <?php if ($puoFarePrestiti): ?>
                <a href="libri.php" class="btn-catalogo">
                    Vai al Catalogo per Nuovi Prestiti
                </a>
            <?php else: ?>
                <button class="btn-catalogo-disabled" disabled title="Devi restituire almeno un libro prima di fare nuovi prestiti">
                    Vai al Catalogo per Nuovi Prestiti
                </button>
                <p style="color: #856404; margin-top: 10px; font-size: 14px;">
                    Restituisci almeno un libro per poter fare nuovi prestiti
                </p>
            <?php endif; ?>
        </div>
        
        <?php if (count($prestiti) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Autore</th>
                        <th>Data Prestito</th>
                        <th>Data Scadenza</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestiti as $prestito): 
                        $scaduto = strtotime($prestito['dataScadenza']) < time();
                    ?>
                        <tr class="<?= $scaduto ? 'scaduto' : '' ?>">
                            <td><strong><?= htmlspecialchars($prestito['titolo']) ?></strong></td>
                            <td><?= htmlspecialchars($prestito['autore']) ?></td>
                            <td><?= date('d/m/Y', strtotime($prestito['dataPrestito'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($prestito['dataScadenza'])) ?></td>
                            <td>
                                <?php if ($scaduto): ?>
                                    <strong style="color: #721c24;">SCADUTO</strong>
                                <?php else: ?>
                                    <span style="color: #155724;">In corso</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h2>Nessun prestito attivo</h2>
                <p>Esplora il catalogo per trovare qualcosa di interessante!</p>
                <a href="libri.php" class="btn-catalogo">Vai al Catalogo</a>
            </div>
        <?php endif; ?>
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
