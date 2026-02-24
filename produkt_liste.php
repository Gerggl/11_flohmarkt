<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$sql = "SELECT a.id, a.titel, a.preis, a.bild_pfad, a.bid, k.bezeichnung AS kategorie
        FROM artikel a
        LEFT JOIN kategorie k ON a.kid = k.kid
        ORDER BY a.id DESC";

$stmt = $pdo->query($sql);
$produkte = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Produktübersicht | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">

        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <span class="section-label">Inventar</span>
                <h1>Produktübersicht</h1>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-image">Bild</th>
                            <th>Titel</th>
                            <th>Kategorie</th>
                            <th>Preis</th>
                            <th class="text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produkte) === 0): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    Keine Produkte vorhanden.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($produkte as $produkt): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($produkt['bild_pfad'])): ?>
                                        <img src="<?= htmlspecialchars($produkt['bild_pfad']) ?>" class="product-image-thumb" alt="Bild">
                                    <?php else: ?>
                                        <div class="product-image-thumb inline-flex items-center text-center text-sm" style="background: #e2e8f0; color: #94a3b8; justify-content: center;">🖼️</div>
                                    <?php endif; ?>
                                </td>
                                <td class="font-weight-600"><?= htmlspecialchars($produkt['titel']) ?></td>
                                <td><span class="cat-pill"><?= htmlspecialchars($produkt['kategorie'] ?? '-') ?></span></td>
                                <td class="font-weight-700"><?= number_format($produkt['preis'], 2, ',', '.') ?> €</td>
                                <td class="text-right">
                                    <div class="button-stack" style="gap: 0.5rem; justify-content: flex-end;">
                                        <a href="produkt_detail.php?id=<?= $produkt['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem; width: auto;">
                                            Details
                                        </a>
                                        <?php if (isset($_SESSION['user_id']) && isset($produkt['bid']) && $_SESSION['user_id'] == $produkt['bid']): ?>
                                            <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; width: auto;">
                                                Bearbeiten
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>
