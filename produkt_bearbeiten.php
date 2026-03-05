<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: produkt_liste.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM artikel WHERE id = :id");
$stmt->execute(['id' => $id]);
$artikel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artikel) {
    header("Location: produkt_liste.php");
    exit;
}

if ($artikel['bid'] != $_SESSION['user_id']) {
    header("Location: produkt_liste.php");
    exit;
}

$sql_kat = "SELECT kid, bezeichnung FROM kategorie ORDER BY bezeichnung ASC";
$stmt_kat = $pdo->query($sql_kat);
$kategorien = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel        = trim($_POST['titel'] ?? '');
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    $preis        = trim($_POST['preis'] ?? '');
    $kid          = $_POST['kid'] ?? '';

    // Verbotene Begriffe prüfen (z.B. Waffen, Drogen)
    require_once("function.php");

    if (enthaeltVerboteneWoerter($titel) || enthaeltVerboteneWoerter($beschreibung)) {
        $error = "Ups! Dein Inserat enthält Begriffe, die auf unserem Flohmarkt nicht erlaubt sind. Bitte überprüfe Titel und Beschreibung auf unzulässige Inhalte (z.B. Waffen oder Ähnliches).";
    } elseif ($titel === '' || $beschreibung === '' || $preis === '' || $preis < 0 || $kid === '') {
        $error = "Bitte alle Pflichtfelder korrekt ausfüllen.";
    }

    $bildPfad = $artikel['bild_pfad'];

    if (!isset($error) && isset($_FILES['bild']) && $_FILES['bild']['error'] === UPLOAD_ERR_OK) {
        $erlaubteMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['bild']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $erlaubteMimeTypes)) {
            $error = "Nur JPG, PNG oder GIF Bilder sind erlaubt.";
        } else {
            if (!file_exists('uploads')) mkdir('uploads', 0777, true);
            $endung = pathinfo($_FILES['bild']['name'], PATHINFO_EXTENSION);
            $dateiname = uniqid('img_', true) . '.' . $endung;
            $zielPfad = 'uploads/' . $dateiname;

            if (move_uploaded_file($_FILES['bild']['tmp_name'], $zielPfad)) {
                $bildPfad = $zielPfad;
            } else {
                $error = "Fehler beim Speichern des Bildes.";
            }
        }
    }

    if (!isset($error)) {
        $sql = "UPDATE artikel 
                SET titel = :titel, 
                    beschreibung = :beschreibung, 
                    preis = :preis, 
                    bild_pfad = :bild_pfad, 
                    kid = :kid
                WHERE id = :id AND bid = :bid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titel'        => $titel,
            ':beschreibung' => $beschreibung,
            ':preis'        => $preis,
            ':bild_pfad'    => $bildPfad,
            ':kid'          => (int)$kid,
            ':id'           => $id,
            ':bid'          => $_SESSION['user_id']
        ]);

        header("Location: produkt_detail.php?id=" . $id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bearbeiten</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Verwaltung</p>
                <h1>Produkt bearbeiten</h1>
            </div>

            <div style="max-width: 800px;">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="glass-panel" style="padding: 2.5rem;">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group full-col">
                            <label for="titel">Produktbezeichnung</label>
                            <input type="text" name="titel" id="titel" class="input-control" required value="<?= htmlspecialchars($_POST['titel'] ?? $artikel['titel']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="kid">Kategorie</label>
                            <select name="kid" id="kid" class="input-control" required>
                                <?php foreach ($kategorien as $kat): ?>
                                    <option value="<?= $kat['kid'] ?>" <?= ((isset($_POST['kid']) && $_POST['kid'] == $kat['kid']) || (!isset($_POST['kid']) && $artikel['kid'] == $kat['kid'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['bezeichnung']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="preis">Preisvorstellung (€)</label>
                            <input type="number" min="1" name="preis" id="preis" step="1" class="input-control" required value="<?= htmlspecialchars($_POST['preis'] ?? $artikel['preis']) ?>">
                        </div>

                        <div class="form-group full-col">
                            <label for="beschreibung">Detaillierte Beschreibung</label>
                            <textarea name="beschreibung" id="beschreibung" rows="5" class="input-control" style="height: auto; min-height: 120px;"><?= htmlspecialchars($_POST['beschreibung'] ?? $artikel['beschreibung']) ?></textarea>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label>Foto ändern (optional)</label>
                        <input type="file" name="bild" class="input-control" accept="image/*" style="padding: 1rem; border-style: dashed;">
                    </div>

                    <div style="margin-top: 3rem; display: flex; gap: 1rem;">
                        <a href="produkt_detail.php?id=<?= $id ?>" class="btn btn-secondary" style="flex: 1;">Abbrechen</a>
                        <button type="submit" class="btn btn-primary" style="flex: 2;">Änderungen Speichern</button>
                    </div>
                </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>

