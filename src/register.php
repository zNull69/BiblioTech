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

$messaggio = '';
$authCodeGenerato = '';
$registrazioneCompletata = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $codiceFiscale = strtoupper($_POST['codiceFiscale'] ?? '');
    $ruolo = 'studente';
    
    $stmt = mysqli_prepare($conn, "SELECT IdUtente FROM Utente WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $messaggio = "Email già registrata";
    } else {
        $val1 = mt_rand() / mt_getrandmax();
        $val2 = mt_rand() / mt_getrandmax();
        $val3 = mt_rand() / mt_getrandmax();
        
        $somma1 = $val1 + $val2;
        $somma2 = $val2 + $val3;
        
        $authCode = (int)(($somma1 + $somma2) * 100000);
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $authCodeHash = password_hash($authCode, PASSWORD_DEFAULT);
        
        $stmt = mysqli_prepare($conn, "INSERT INTO Utente (nome, cognome, email, password, authCode, codiceFiscale, ruolo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssss", $nome, $cognome, $email, $passwordHash, $authCodeHash, $codiceFiscale, $ruolo);
        mysqli_stmt_execute($stmt);
        
        $authCodeGenerato = $authCode;
        $registrazioneCompletata = true;
    }
    mysqli_stmt_close($stmt);

}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Registrazione</title>
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
                <p class="subtitle">Registrazione nuovo utente</p>
                
                <?php if ($registrazioneCompletata): ?>
                <div class="successo">
                    <h2 style="margin-bottom: 15px;">Registrazione effettuata!</h2>
                    <p style="margin-bottom: 20px;">Il tuo account è stato attivato con successo.</p>
                </div>
                
                <div class="warning">
                    <strong>ATTENZIONE:</strong> Salva il tuo codice di autenticazione in un luogo sicuro. 
                    Questo codice verrà mostrato <strong>UNA SOLA VOLTA</strong> e sarà necessario per ogni accesso insieme a email e password.
                </div>
                
                <div class="authcode-box">
                    <p style="text-align: center; font-weight: bold; color: #856404; margin-bottom: 10px;">
                        IL TUO CODICE DI AUTENTICAZIONE:
                    </p>
                    <div class="authcode-display">
                        <?= $authCodeGenerato ?>
                    </div>
                    <p style="text-align: center; font-size: 12px; color: #856404; margin-top: 10px;">
                        Annotalo ora! Non potrai più visualizzarlo.
                    </p>
                </div>
                
                <div class="link">
                    <a href="login.php">→ Vai al Login</a>
                </div>
                
                <?php else: ?>
                    <?php if ($messaggio): ?>
                        <div class="errore"><?= htmlspecialchars($messaggio) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                            
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="inputNome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="inputNome" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="inputCognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="inputCognome" name="cognome" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="inputEmail" class="form-label">Indirizzo Email</label>
                            <input type="email" class="form-control" id="inputEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="inputPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="inputPassword" name="password" required>
                        </div>

                        <div class="mb-4">
                            <label for="inputCF" class="form-label">Codice Fiscale</label>
                            <input type="text" class="form-control font-monospace text-uppercase" id="inputCF" name="codiceFiscale" maxlength="16" required>
                            <div class="form-text">Richiesto per il calcolo dell'età per consentire un'accesso facilitato ai libri più appropriati per te</div>
                        </div>

                        <button type="submit" class="w-100 btn btn-lg btn-bibliotech">Registrati al portale</button>
                    </form>
                    <div class="link">
                        <a href="login.php">Hai già un account? Accedi</a>
                    </div>
                <?php endif; ?>
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
