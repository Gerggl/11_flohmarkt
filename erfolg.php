<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erfolg</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="app-main">
        <div class="form-header">
            <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">System</p>
            <h1>Veröffentlicht</h1>
            <p>Ihr Produkt wurde erfolgreich in die Datenbank aufgenommen.</p>
        </div>

        <div class="glass-panel" style="padding: 2.5rem; max-width: 600px; text-align: center;">
            <p style="margin-bottom: 2.5rem; color: var(--text-muted); font-size: 1.1rem;">
                Das Inserat ist nun für alle Mitglieder der Community im Marktplatz sichtbar.
            </p>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="produkt_liste.php" class="btn btn-primary" style="flex: 1;">Zur Kollektion</a>
                <a href="produkt_erstellen.php" class="btn btn-secondary" style="flex: 1;">Weiteres Inserat</a>
            </div>
        </div>
    </main>
</div>

</body>
</html>
