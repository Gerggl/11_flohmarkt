<?php
session_start();
if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}

$email = isset($_GET['email']) ? $_GET['email'] : '';
$titel = isset($_GET['titel']) ? $_GET['titel'] : 'Artikel';

$subject = "Interesse an Artikel: " . $titel;
$mailto_link = "mailto:" . $email . "?subject=" . rawurlencode($subject);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anfrage gesendet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.location.href = "<?= $mailto_link ?>";
            }, 800);
        };
    </script>
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">System-Bestätigung</p>
                <h1>Anfrage initiiert</h1>
            </div>

            <div class="glass-panel" style="padding: 4rem 2rem; max-width: 700px; text-align: center; margin-top: 1rem; position: relative; overflow: hidden;">

                <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: var(--primary-light); border-radius: 50%; opacity: 0.5; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="success-icon" style="background: var(--primary-light); color: var(--primary); border: 2px solid rgba(126, 142, 122, 0.2);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2L11 13"></path>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                        </svg>
                    </div>

                    <h2 style="margin-bottom: 1.5rem; color: var(--text-main); font-size: 2.25rem;">E-Mail wird versendet</h2>
                    
                    <p style="color: var(--text-muted); font-size: 1.15rem; line-height: 1.7; margin-bottom: 3rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Wir haben Ihr Interesse für <strong>"<?= htmlspecialchars($titel) ?>"</strong> registriert. <br>
                        Ihr Standard-E-Mail-Programm öffnet sich jetzt automatisch mit der Adresse des Verkäufers.
                    </p>

                    <div style="background: var(--bg-app); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 3rem; display: inline-block; min-width: 300px;">
                        <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem; letter-spacing: 0.05em;">Empfänger-Adresse</span>
                        <span style="font-family: 'Inter', sans-serif; font-size: 1.25rem; color: var(--primary); font-weight: 700;"><?= htmlspecialchars($email) ?></span>
                    </div>

                    <div style="display: flex; gap: 1.25rem; justify-content: center; max-width: 500px; margin: 0 auto;">
                        <a href="produkt_liste.php" class="btn btn-secondary" style="flex: 1;">Zur Kollektion</a>
                        <a href="<?= $mailto_link ?>" class="btn btn-primary" style="flex: 1.5;">E-Mail manuell öffnen</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>

