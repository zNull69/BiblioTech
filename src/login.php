<?php
require_once 'config.php';
if (isset($_SESSION['IdUtente'])) {
    $ruolo = getRuolo($conn, $_SESSION['IdUtente']);
    if ($ruolo === 'admin') {
        header('Location: gestione_restituzioni.php');
    } else {
        header('Location: libri.php');
    }
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $authCode = $_POST['authCode'] ?? '';
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM Utente WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $utente = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($utente && password_verify($password, $utente['password']) && password_verify($authCode, $utente['authCode'])) {
        $tokenSessione = bin2hex(random_bytes(32));
        $scadenzaSessione = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        $stmt = mysqli_prepare($conn, "INSERT INTO Sessione (IdUtente, tokenSessione, lastLogin, scadenzaSessione) VALUES (?, ?, NOW(), ?)");
        mysqli_stmt_bind_param($stmt, "iss", $utente['IdUtente'], $tokenSessione, $scadenzaSessione);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $_SESSION['IdUtente'] = $utente['IdUtente'];
        $_SESSION['tokenSessione'] = $tokenSessione;
        
        if ($utente['ruolo'] === 'admin') {
            header('Location: gestione_restituzioni.php');
        } else {
            header('Location: prestiti.php');
        }
        exit;
    } else {
        $errore = 'Credenziali non valide';
    }
}


?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Login</title>

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
        </div>
    </nav>
    <main>
    <div class="content-side">
        <div class="form-container glass">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="static/CSS/imgs/logo.png" alt="BiblioTech Logo" height="100" class="rounded px-1" />
                <span><h2>BiblioTech</h2></span>
            </a>
            <?php if ($errore): ?>
                <div class="errore"><?= htmlspecialchars($errore) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="inputEmail" class="form-label">Indirizzo Email</label>
                    <input type="email" class="form-control" id="inputEmail" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="inputPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="inputPassword" name="password" required>
                </div>

                <div class="mb-4">
                    <label for="inputCode" class="form-label">Codice Univoco</label>
                    <input type="text" class="form-control font-monospace" id="inputCode" name="authCode" placeholder="XXX-XXX" required>
                    <div class="form-text">Inserisci il codice ricevuto in fase di registrazione.</div>
                </div>

                <button class="w-100 btn btn-lg btn-bibliotech" type="submit">Accedi al portale</button>
            </form>
            <div class="link">
                <a href="register.php">Non hai un account? Registrati</a>
            </div>
        </div>
    </div>
    <div class="side-img"></div>
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
