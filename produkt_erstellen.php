<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$sql_kat = "SELECT kid, bezeichnung FROM kategorie ORDER BY bezeichnung ASC";
$stmt_kat = $pdo->query($sql_kat);
$kategorien = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titel        = trim($_POST['titel'] ?? '');
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    $preis        = trim($_POST['preis'] ?? '');
    $kid          = $_POST['kid'] ?? '';

    //Aufruf der Funktion zur Prüfung, ob verbotene Worte verwendet wurden.
    require_once("function.php");

    if (enthaeltVerboteneWoerter($titel) || enthaeltVerboteneWoerter($beschreibung)) {
        $error = "Ups! Dein Inserat enthält Begriffe, die auf unserem Flohmarkt nicht erlaubt sind. Bitte überprüfe Titel und Beschreibung auf unzulässige Inhalte (z.B. Waffen oder Ähnliches).";
    } elseif ($titel === '' || $beschreibung === '' || $preis === '' || $preis < 0 || $kid === '') {
        $error = "Bitte alle Pflichtfelder korrekt ausfüllen.";
    }

    $bildPfad = null;
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
        $sql = "INSERT INTO artikel 
            (titel, beschreibung, preis, bild_pfad, kid, bid) 
            VALUES (:titel, :beschreibung, :preis, :bild_pfad, :kid, :bid)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titel'        => $titel,
            ':beschreibung' => $beschreibung,
            ':preis'        => $preis,
            ':bild_pfad'    => $bildPfad,
            ':kid'          => (int)$kid,
            ':bid'          => $_SESSION['user_id']
        ]);

        header("Location: erfolg.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Inserat</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Marktplatz</p>
                <h1>Produkt einstellen</h1>
            </div>

            <div style="max-width: 801px;">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="glass-panel" style="padding: 2.5rem;">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group full-col">
                                <label for="titel">Titel des Inserats</label>
                                <input type="text" name="titel" id="titel" class="input-control" required value="<?= htmlspecialchars($_POST['titel'] ?? '') ?>" placeholder="z.B. Vintage Ledersessel">
                            </div>

                            <div class="form-group">
                                <label for="kid">Kategorie</label>
                                <select name="kid" id="kid" class="input-control" required>
                                    <option value="" disabled <?= !isset($_POST['kid']) ? 'selected' : '' ?>>Auswählen...</option>
                                    <?php foreach ($kategorien as $kat): ?>
                                        <option value="<?= $kat['kid'] ?>" <?= (isset($_POST['kid']) && $_POST['kid'] == $kat['kid']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['bezeichnung']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preis">Vorgeschlagener Preis (€)</label>
                                <input type="number" min="1" name="preis" id="preis" step="1" class="input-control" required value="<?= htmlspecialchars($_POST['preis'] ?? '') ?>" placeholder="0.00">
                            </div>

                            <div class="form-group full-col">
                                <label for="beschreibung">Beschreibung Details</label>
                                <textarea name="beschreibung" id="beschreibung" rows="5" class="input-control" style="height: auto; min-height: 120px;" placeholder="Beschreiben Sie den Zustand, das Alter und Besonderheiten..."><?= htmlspecialchars($_POST['beschreibung'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label>Produktfoto hochladen</label>
                            <input type="file" name="bild" class="input-control" accept="image/*">
                        </div>

                        <div style="margin-top: 3rem; display: flex; gap: 1rem;">
                            <a href="produkt_liste.php" class="btn btn-secondary" style="flex: 1;">Abbrechen</a>
                            <button type="submit" class="btn btn-primary" style="flex: 2;">Inserat Veröffentlichen</button>
                        </div>
                    </form>
                </div>
        </main>
    </div>

</body>

</html>