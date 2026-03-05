<?php
session_start();
if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$artikel_id = isset($_GET['artikel_id']) ? (int)$_GET['artikel_id'] : (isset($_POST['artikel_id']) ? (int)$_POST['artikel_id'] : 0);
$kaeufer_id = isset($_GET['kaeufer_id']) ? (int)$_GET['kaeufer_id'] : (isset($_POST['kaeufer_id']) ? (int)$_POST['kaeufer_id'] : 0);

if ($artikel_id <= 0 || $kaeufer_id <= 0) {
    header('Location: meine_artikel.php');
    exit;
}

$sql_check = "SELECT * FROM artikel WHERE id = :id AND bid = :user_id";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute(['id' => $artikel_id, 'user_id' => $_SESSION['user_id']]);
$artikel = $stmt_check->fetch();

if (!$artikel) {
    header('Location: meine_artikel.php');
    exit;
}

$sql_kaeufer = "SELECT * FROM benutzer WHERE bid = :bid";
$stmt_kaeufer = $pdo->prepare($sql_kaeufer);
$stmt_kaeufer->execute(['bid' => $kaeufer_id]);
$kaeufer = $stmt_kaeufer->fetch();

if (!$kaeufer) {
    header('Location: meine_artikel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bestaetigen'])) {
    try {
        $sql_update = "UPDATE artikel SET gekauft_von = :kaeufer_id WHERE id = :artikel_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['kaeufer_id' => $kaeufer_id, 'artikel_id' => $artikel_id]);
        
        header('Location: meine_artikel.php?status=success');
        exit;
    } catch (PDOException $e) {
        $error = "Fehler beim Aktualisieren der Datenbank. Stellen Sie sicher, dass das Feld 'gekauft_von' in der Tabelle 'artikel' existiert.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Verkauf bestätigen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Transaktion</p>
                <h1>Verkauf bestätigen</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel" style="max-width: 650px; padding: 2.5rem; margin-top: 2rem;">
                <div style="margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h3 style="color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.25rem;">Artikel Details</h3>
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <?php if (!empty($artikel['bild_pfad'])): ?>
                            <img src="<?= htmlspecialchars($artikel['bild_pfad']) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color);" alt="Bild">
                        <?php endif; ?>
                        <div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); line-height: 1.2;"><?= htmlspecialchars($artikel['titel']) ?></div>
                            <div style="font-size: 1.1rem; color: var(--primary); font-weight: 600; margin-top: 0.25rem;"><?= number_format($artikel['preis'], 2, ',', '.') ?> €</div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 3rem;">
                    <h3 style="color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.25rem;">Käufer Informationen</h3>
                    
                    <div style="background: rgba(255,255,255,0.02); border-radius: 16px; padding: 1.5rem; display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; border: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Vollständiger Name</label>
                            <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($kaeufer['vorname'] . ' ' . $kaeufer['nachname']) ?></div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">E-Mail Adresse</label>
                            <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($kaeufer['email']) ?></div>
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Anschrift</label>
                            <div style="font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars($kaeufer['strasse']) ?><br>
                                <?= htmlspecialchars($kaeufer['plz']) ?> <?= htmlspecialchars($kaeufer['ort']) ?>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Telefonnummer</label>
                            <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($kaeufer['telefon'] ?: 'Keine Angabe') ?></div>
                        </div>
                    </div>
                </div>

                <form action="" method="POST" style="display: flex; gap: 1rem;">
                    <input type="hidden" name="artikel_id" value="<?= $artikel_id ?>">
                    <input type="hidden" name="kaeufer_id" value="<?= $kaeufer_id ?>">
                    <a href="meine_artikel.php" class="btn btn-secondary" style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center;">Abbrechen</a>
                    <button type="submit" name="bestaetigen" class="btn btn-primary" style="flex: 2; font-weight: 700; padding: 1rem;">Verkauf abschließen</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>

