<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erfolg | Obsidian Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">
    
    <aside class="app-sidebar">
        <div class="brand-logo brand-dark mb-12">
            <img src="bilder/logo.png" alt="Logo">
            <span>Flohmarkt</span>
        </div>
        <nav>
            <a href="produkt_liste.php" class="nav-link">Kollektion</a>
            <a href="produkt_erstellen.php" class="nav-link">Neues Inserat</a>
            <a href="abmelden.php" class="nav-link">Abmelden</a>
        </nav>
    </aside>

    <main class="app-main">
        <span class="section-label">System-Bestätigung</span>
        <h1>Veröffentlicht.</h1>

        <div class="detail-grid">
            
            <div class="interaction-area">
                <p>
                    Ihr Produkt wurde erfolgreich in die Datenbank aufgenommen und ist nun für alle Mitglieder der Community im Marktplatz sichtbar.
                </p>

                <div class="button-stack">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='produkt_liste.php'">Zur Kollektion</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='produkt_erstellen.php'">Weiteres Listing</button>
                </div>
            </div>

            <div class="visual-part">
                <div class="success-card">
                    <div class="success-icon">✦</div>
                    <div class="success-text-large">Eintrag Gespeichert</div>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
