<?php
session_start();

$message = '';
$status = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once('db.php');

    $email = trim($_POST['email']);
    $password = $_POST['passwort'];

    try {
        $stmt = $pdo->prepare(
            'SELECT bid, vorname, nachname, geburtsdatum, geschlecht, email, strasse, plz, ort, telefon, passwort, agb
             FROM benutzer
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Datenbankfehler: ' . $e->getMessage());
    }


    if ($user && password_verify($password, $user['passwort'])) {


        if (password_needs_rehash($user['passwort'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare(
                'UPDATE benutzer
                 SET passwort = :passwort
                 WHERE bid = :bid'
            );
            $updateStmt->execute([
                'passwort' => $newHash,
                'bid'      => $user['bid']
            ]);
        }


        session_regenerate_id(true);


        $_SESSION['user_id']      = $user['bid'];
        $_SESSION['vorname']      = $user['vorname'];
        $_SESSION['nachname']     = $user['nachname'];
        $_SESSION['geburtsdatum'] = $user['geburtsdatum'];
        $_SESSION['geschlecht']   = $user['geschlecht'];
        $_SESSION['email']        = $user['email'];
        $_SESSION['strasse']      = $user['strasse'];
        $_SESSION['plz']          = $user['plz'];
        $_SESSION['ort']          = $user['ort'];
        $_SESSION['telefon']      = $user['telefon'];
        $_SESSION['agb']          = $user['agb'];
        $_SESSION['eingeloggt']   = true;

        $message = 'Erfolgreich eingeloggt! Hallo ' . htmlspecialchars($user['vorname']) . '!';
        $status = 'success';

        header('Location: dashboard.php');
        exit;

    } else {
        $message = 'E-Mail oder Passwort ist falsch.';
        $status = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Flohmarkt</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="auth-sidebar">
                <div class="brand-section">
                    <div class="brand-logo">
                        <img src="bilder/logo.png" alt="Logo">
                        <span>Flohmarkt</span>
                    </div>
                    <h2>Willkommen zurück!</h2>
                    <p class="text-white text-opacity-90">Schön, dass Sie wieder da sind. Enzo & Gerald freuen sich auf Ihren Besuch!</p>
                </div>

                <ul class="benefits-list">
                    <li>Schneller Zugriff auf Inserate</li>
                    <li>Exklusive Angebote</li>
                    <li>Persönliche Merkliste</li>
                </ul>

                <div class="sidebar-footer">
                    <p class="text-opacity-70 text-sm">© 2026 Flohmarkt Community<br>Sicher & geschützt</p>
                </div>
            </div>

            <div class="auth-content">
                <form action="" method="post">
                    <div class="form-header">
                        <h1>Anmelden</h1>
                        <p class="text-sub">Geben Sie Ihre E-Mail und Ihr Passwort ein.</p>

                        <?php if (!empty($message)): ?>
                            <div class="alert <?= ($status === 'success') ? 'alert-success' : 'alert-error' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div class="floating-label">
                            <input type="email" name="email" id="email" class="input-control" placeholder=" " required>
                            <label for="email">E-Mail Adresse</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="floating-label">
                            <input type="password" name="passwort" id="passwort" class="input-control" placeholder=" " required>
                            <label for="passwort">Passwort</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Sicher anmelden</button>

                    <div class="footer-text">
                        Noch keinen Account? <a href="registrierung.php">Hier registrieren</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

</body>
</html>
