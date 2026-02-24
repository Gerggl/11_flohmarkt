<?php
session_start();
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
    header("Location: produkt_liste.php");
    exit;
}

$message_status = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $nachricht = "";
    if (isset($_POST['kontakt_senden'])) {
        $nachricht = "Interesse";
        $message_status = 'Anfrage wurde gesendet.';
    } elseif (isset($_POST['kauf_senden'])) {
        $nachricht = "Kaufanfrage";
        $message_status = 'Kaufanfrage wurde gesendet.';
    }

    if ($nachricht) {
        try {
            $sql_kontakt = "INSERT INTO interessenten (artikel_id, benutzer_id, zeitpunkt) 
                            VALUES (:artikel_id, :benutzer_id, NOW())";
            $stmt_kontakt = $pdo->prepare($sql_kontakt);
            $stmt_kontakt->execute([
                'artikel_id' => $produkt_id,
                'benutzer_id' => $_SESSION['user_id']
            ]);
        } catch (PDOException $e) {
            $message_status = 'Fehler beim Senden.';
            $message_type = 'error';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['user_id'])) {
    header('Location: Login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produkt['titel']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <a href="produkt_liste.php" class="back-link">← Zurück zur Kollektion</a>

            <?php if ($message_status): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <?= htmlspecialchars($message_status) ?>
                </div>
            <?php endif; ?>

            <div class="detail-grid">
                
                <div class="image-section">
                    <?php if (!empty($produkt['bild_pfad'])): ?>
                        <img src="<?= htmlspecialchars($produkt['bild_pfad']) ?>" class="product-image-large" alt="Produkt">
                    <?php else: ?>
                        <div class="product-image-large" style="background: var(--bg-main); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                            Kein Bild verfügbar
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <span class="cat-pill">
                        <?= htmlspecialchars($produkt['kategorie'] ?? 'Allgemein') ?>
                    </span>
                    
                    <h1 style="margin-top: 1rem; font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em;"><?= htmlspecialchars($produkt['titel']) ?></h1>
                    
                    <div style="margin: 2rem 0; color: var(--text-muted); font-size: 1.1rem; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($produkt['beschreibung'] ?? '')) ?>
                    </div>

                    <div class="stat-card">
                        <span class="stat-label">Preisvorstellung</span>
                        <span class="price-tag"><?= number_format($produkt['preis'], 2, ',', '.') ?> €</span>
                        
                        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem;">
                            <?php if (isset($_SESSION['user_id']) && isset($produkt['bid']) && $_SESSION['user_id'] == $produkt['bid']): ?>
                                <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-primary w-full">Inserat bearbeiten</a>
                                <a href="meine_artikel.php" class="btn btn-secondary w-full">In Meinen Artikeln verwalten</a>
                            <?php else: ?>
                                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                                    <button type="submit" name="kontakt_senden" class="btn btn-contact w-full">Verkäufer kontaktieren</button>
                                    <button type="submit" name="kauf_senden" class="btn btn-flohmarkt w-full">Produkt kaufen</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
