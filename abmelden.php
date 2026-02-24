<?php
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auf Wiedersehen | Flohmarkt</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card" style="max-height: 600px;">
            <div class="auth-sidebar">
                <div class="brand-section">
                    <div class="brand-logo">
                        <img src="bilder/logo.png" alt="Logo">
                        <span>Flohmarkt</span>
                    </div>
                    <h2>Bis bald!</h2>
                    <p class="text-white text-opacity-90">Wir hoffen, Sie hatten einen erfolgreichen Besuch.</p>
                </div>
                
                <div class="sidebar-footer">
                    <p class="text-opacity-70 text-sm">© 2026 Flohmarkt Community<br>Jederzeit bereit für Sie.</p>
                </div>
            </div>

            <div class="auth-content text-center" style="align-items: center; text-align: center;">
                <div class="form-header" style="margin-bottom: 2rem;">
                    <h1>Erfolgreich abgemeldet</h1>
                    <p class="text-sub">Sie wurden sicher ausgeloggt.</p>
                </div>

                <div class="success-icon" style="font-size: 4rem; margin-bottom: 2rem;">👋</div>
                
                <a href="Login.php" class="btn btn-primary" style="max-width: 300px;">Erneut anmelden</a>

                <div class="footer-text">
                    <a href="Login.php">Zurück zum Login</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>