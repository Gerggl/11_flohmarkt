<?php
require_once 'db.php';


$produkt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produkt_id <= 0) {
    header("Location: produkt_liste.php");
    exit;
}


$sql = "SELECT a.*, k.bezeichnung AS kategorie
        FROM artikel a
        LEFT JOIN kategorie k ON a.kid = k.kid
        WHERE a.id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $produkt_id]);
$produkt = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$produkt) {
    die("Produkt nicht gefunden.");
}

// Kontakt-Logik
$message_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $nachricht = "";
    if (isset($_POST['kontakt_senden'])) {
        $nachricht = "Interesse an " . $produkt['titel'];
        $message_status = 'Anfrage wurde erfolgreich gesendet!';
    } elseif (isset($_POST['kauf_senden'])) {
        $nachricht = "KAUFANFRAGE für " . $produkt['titel'] . " (Preis: " . number_format($produkt['preis'], 2, ',', '.') . " €)";
        $message_status = 'Kaufanfrage wurde erfolgreich gesendet!';
    }

    if ($nachricht) {
        try {
            $sql_kontakt = "INSERT INTO anfragen (artikel_id, absender_id, nachricht) 
                            VALUES (:artikel_id, :absender_id, :nachricht)";
            $stmt_kontakt = $pdo->prepare($sql_kontakt);
            $stmt_kontakt->execute([
                'artikel_id' => $produkt_id,
                'absender_id' => $_SESSION['user_id'],
                'nachricht' => $nachricht
            ]);
        } catch (PDOException $e) {
            $message_status = 'Fehler beim Senden der Anfrage.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produkt['titel']) ?> | Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">

        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <a href="produkt_liste.php" class="back-link">
                <span>←</span> Zurück zur Übersicht
            </a>

            <?php if ($message_status): ?>
                <div class="alert alert-success mt-4">
                    <?= htmlspecialchars($message_status) ?>
                </div>
            <?php endif; ?>

            <div class="detail-grid">
                
                <div class="image-section">
                    <?php if (!empty($produkt['bild_pfad'])): ?>
                        <img src="<?= htmlspecialchars($produkt['bild_pfad']) ?>" class="product-image-large" alt="<?= htmlspecialchars($produkt['titel']) ?>">
                    <?php else: ?>
                        <div class="image-placeholder">
                            📷
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <span class="category-badge">
                        <?= htmlspecialchars($produkt['kategorie'] ?? 'Allgemein') ?>
                    </span>
                    
                    <h1 class="detail-title"><?= htmlspecialchars($produkt['titel']) ?></h1>
                    
                    <div class="detail-desc">
                        <?= nl2br(htmlspecialchars($produkt['beschreibung'] ?? 'Keine Beschreibung verfügbar.')) ?>
                    </div>

                    <div class="price-box">
                        <div class="price-label">Preisvorstellung</div>
                        <div class="price-tag"><?= number_format($produkt['preis'], 2, ',', '.') ?> €</div>
                        
                        <?php if (isset($_SESSION['user_id']) && isset($produkt['bid']) && $_SESSION['user_id'] == $produkt['bid']): ?>
                            <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-secondary w-full p-4 mt-4" style="text-align: center;">
                                Produkt bearbeiten
                            </a>
                        <?php else: ?>
                            <form action="" method="POST">
                                <button type="submit" name="kontakt_senden" class="btn btn-primary w-full p-4 mt-4">
                                    Verkäufer kontaktieren
                                </button>
                                <button type="submit" name="kauf_senden" class="btn btn-flohmarkt w-full p-4 mt-4">
                                    Produkt kaufen
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
