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
    <title>Kollektion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Marktplatz</p>
                <h1>Kollektion</h1>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Vorschau</th>
                            <th>Bezeichnung</th>
                            <th>Kategorie</th>
                            <th>Preis</th>
                            <th class="text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produkte)): ?>
                            <tr>
                                <td colspan="5" style="padding: 4rem; text-align: center; color: var(--text-muted);">
                                    Aktuell sind keine Produkte in der Kollektion vorhanden.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($produkte as $produkt): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($produkt['bild_pfad'])): ?>
                                        <img src="<?= htmlspecialchars($produkt['bild_pfad']) ?>" class="product-image-thumb" alt="Bild">
                                    <?php else: ?>
                                        <div class="product-image-thumb" style="background: var(--bg-main);"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($produkt['titel']) ?></div>
                                    <?php if (isset($_SESSION['user_id']) && $produkt['bid'] == $_SESSION['user_id']): ?>
                                        <span style="font-size: 0.7rem; color: var(--primary); font-weight: 700;">DEIN INSERAT</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="cat-pill"><?= htmlspecialchars($produkt['kategorie'] ?? 'Allgemein') ?></span></td>
                                <td style="font-weight: 600; color: var(--primary);"><?= number_format($produkt['preis'], 2, ',', '.') ?> €</td>
                                <td class="text-right">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <a href="produkt_detail.php?id=<?= $produkt['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Details</a>
                                        <?php if (isset($_SESSION['user_id']) && $produkt['bid'] == $_SESSION['user_id']): ?>
                                            <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Bearbeiten</a>
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
