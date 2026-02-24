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
            'SELECT bid, vorname, nachname, email, passwort
             FROM benutzer
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Datenbankfehler');
    }

    if ($user && password_verify($password, $user['passwort'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['bid'];
        $_SESSION['vorname']    = $user['vorname'];
        $_SESSION['nachname']   = $user['nachname'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['eingeloggt'] = true;

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
    <title>Anmelden</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-sidebar">
                <h2>Willkommen zurück</h2>
                <p>Melden Sie sich an, um Ihre Artikel zu verwalten und Anfragen einzusehen.</p>
            </div>

            <div class="auth-content">
                <form action="" method="post">
                    <div class="form-header">
                        <div class="brand-logo" style="margin-bottom: 2rem;">
                            <img src="bilder/logo.png" alt="Logo">
                            <span>Flohmarkt</span>
                        </div>
                        <h1>Anmelden</h1>
                        <?php if (!empty($message)): ?>
                            <div class="alert <?= ($status === 'success') ? 'alert-success' : 'alert-error' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" name="email" id="email" class="input-control" required placeholder="Ihr@beispiel.de">
                    </div>

                    <div class="form-group">
                        <label for="passwort">Passwort</label>
                        <input type="password" name="passwort" id="passwort" class="input-control" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Anmelden</button>

                    <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted); text-align: center;">
                        Noch keinen Account? <a href="Registrierung.php" style="color: var(--primary); font-weight: 600;">Registrieren</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
