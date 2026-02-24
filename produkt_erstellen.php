<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

// 1. Kategorien für das Dropdown-Menü aus der DB laden
$sql_kat = "SELECT kid, bezeichnung FROM kategorie ORDER BY bezeichnung ASC";
$stmt_kat = $pdo->query($sql_kat);
$kategorien = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titel        = trim($_POST['titel'] ?? '');
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    $preis        = trim($_POST['preis'] ?? '');
    $kid          = $_POST['kid'] ?? ''; // Wir arbeiten jetzt mit der ID (kid)

    // Grundvalidierung
    if ($titel === '' || $beschreibung === '' || $preis === '' || $preis < 0 || $kid === '') {
        $error = "Bitte alle Pflichtfelder korrekt ausfüllen.";
    }

    $bildPfad = null;
    // ... (dein restlicher Bilder-Upload-Code bleibt gleich)
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
            ':kid'          => $kid,
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
    <title>Listing erstellen | Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <span class="section-label">Inventar-Management</span>
                <h1>Produkt einstellen</h1>
            </div>

            <?php if (isset($error)) : ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group full">
                        <div class="floating-label">
                            <input type="text" name="titel" class="input-control" placeholder=" " required value="<?= htmlspecialchars($_POST['titel'] ?? '') ?>">
                            <label>Titel des Objekts</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="floating-label">
                            <select name="kid" class="input-control" required>
                                <option value="" disabled <?= !isset($_POST['kid']) ? 'selected' : '' ?>>Auswählen...</option>

                                <?php foreach ($kategorien as $kat): ?>
                                    <option value="<?= $kat['kid'] ?>" <?= (isset($_POST['kid']) && $_POST['kid'] == $kat['kid']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['bezeichnung']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                            <label>Kategorie</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="floating-label">
                            <input type="number" min="1" name="preis" step="1" class="input-control" placeholder=" " required value="<?= htmlspecialchars($_POST['preis'] ?? '') ?>">
                            <label>Preis (EUR)</label>
                        </div>
                    </div>

                    <div class="form-group full">
                        <div class="floating-label">
                            <textarea name="beschreibung" rows="5" class="input-control" style="height: auto; min-height: 120px;" placeholder=" "><?= htmlspecialchars($_POST['beschreibung'] ?? '') ?></textarea>
                            <label>Beschreibung</label>
                        </div>
                    </div>
                </div>

                <div class="form-group full mt-4">
                    <label class="font-bold mb-2">Produktfoto</label>
                    <div class="file-upload-zone" id="drop-zone" onclick="document.getElementById('file-upload').click()">
                        <div class="upload-info" id="upload-content">
                            <div class="upload-icon">📷</div>
                            <p class="upload-text-main">Bild hier ablegen oder klicken</p>
                            <p class="upload-text-sub">JPG, PNG oder GIF</p>
                        </div>
                        <input type="file" name="bild" id="file-upload" hidden accept="image/*" onchange="previewImage(this)">
                    </div>
                </div>

                <div class="form-group flex-row gap-4 mt-8" style="display: flex;">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='produkt_liste.php'">Abbrechen</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Veröffentlichen</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        function previewImage(input) {
            const dropZone = document.getElementById('drop-zone');
            const content = document.getElementById('upload-content');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    dropZone.style.backgroundImage = `url(${e.target.result})`;
                    dropZone.style.backgroundSize = 'cover';
                    dropZone.style.backgroundPosition = 'center';
                    content.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>