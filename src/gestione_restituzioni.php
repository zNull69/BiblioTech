<?php
require_once 'config.php';

if (!isset($_SESSION['IdUtente']) || getRuolo($conn, $_SESSION['IdUtente']) !== 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_POST['restituisci'])) {
    $idPrestito = $_POST['idPrestito'];
    $idCopia = $_POST['idCopia'];
    
    mysqli_begin_transaction($conn);
    
    $stmt = mysqli_prepare($conn, "UPDATE Prestito SET dataRestituzione = CURDATE() WHERE IdPrestito = ?");
    mysqli_stmt_bind_param($stmt, "i", $idPrestito);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $stmt = mysqli_prepare($conn, "UPDATE Copia SET stato = 'disponibile' WHERE IdCopia = ?");
    mysqli_stmt_bind_param($stmt, "i", $idCopia);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    mysqli_commit($conn);
    
    $messaggio = "Restituzione registrata con successo!";
}

$query = "SELECT P.IdPrestito, P.IdCopia, L.titolo, L.autore, U.nome, U.cognome, U.email,
          P.dataPrestito, P.dataScadenza
          FROM Prestito P
          INNER JOIN Copia C ON P.IdCopia = C.IdCopia
          INNER JOIN Libro L ON C.IdLibro = L.IdLibro
          INNER JOIN Utente U ON P.IdUtente = U.IdUtente
          WHERE P.dataRestituzione IS NULL
          ORDER BY P.dataScadenza ASC";


$result = mysqli_query($conn, $query);
$prestiti = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Gestione Restituzioni</title>
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
                    <li class="nav-item me-3 border border-light rounded"><a class="nav-link" href="libri.php">Catalogo</a></li>
                    <li class="nav-item me-3 border border-light rounded"><a class="nav-link" href="prestiti.php">I Miei Prestiti</a></li>
                    <li class="nav-item me-3 border border-danger rounded"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="header">
            <div class="header-content">
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="200" class="rounded px-1" />
                    <span><h1>BiblioTech - Gestione restituzioni</h1></span>
                </a>
            </div>
        </div>
        
        <div class="container">
            <?php if (isset($messaggio)): ?>
                <div class="messaggio"><?= htmlspecialchars($messaggio) ?></div>
            <?php endif; ?>
            
            <div class="stats">
                <div class="stat-card">
                    <h3><?= count($prestiti) ?></h3>
                    <p>Prestiti Attivi</p>
                </div>
                <div class="stat-card">
                    <h3><?= count(array_filter($prestiti, function($p) { return strtotime($p['dataScadenza']) < time(); })) ?></h3>
                    <p>Prestiti Scaduti</p>
                </div>
            </div>
            
            <?php if (count($prestiti) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Libro</th>
                                <th>Autore</th>
                                <th>Utente</th>
                                <th>Email</th>
                                <th>Data Prestito</th>
                                <th>Scadenza</th>
                                <th>Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prestiti as $prestito):
                                $scaduto = strtotime($prestito['dataScadenza']) < time();
                            ?>
                                <tr class="<?= $scaduto ? 'table-danger' : '' ?>">
                                    <td><strong><?= htmlspecialchars($prestito['titolo']) ?></strong></td>
                                    <td><?= htmlspecialchars($prestito['autore']) ?></td>
                                    <td><?= htmlspecialchars($prestito['nome'] . ' ' . $prestito['cognome']) ?></td>
                                    <td><?= htmlspecialchars($prestito['email']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prestito['dataPrestito'])) ?></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($prestito['dataScadenza'])) ?>
                                        <?php if ($scaduto): ?>
                                            <br><span class="badge bg-danger mt-1">SCADUTO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="idPrestito" value="<?= $prestito['IdPrestito'] ?>">
                                            <input type="hidden" name="idCopia" value="<?= $prestito['IdCopia'] ?>">
                                            <button type="submit" name="restituisci" class="btn btn-sm btn-restituisci">
                                                Restituisci
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h2>Nessun prestito attivo</h2>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer-bibliotech">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="80" class="rounded px-1" />
            <span><h3>BiblioTech </h3></span>
        </a>
        <p>Un progetto di Francesco Tenerelli - V ITIA A - &copy tutti i diritti riservati</p>
    </footer>
</body>
</html>
