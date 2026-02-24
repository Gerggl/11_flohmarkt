<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch only articles belonging to the logged-in user
$sql = "SELECT a.id, a.titel, a.preis, a.bild_pfad, k.bezeichnung AS kategorie
        FROM artikel a
        LEFT JOIN kategorie k ON a.kid = k.kid
        WHERE a.bid = :user_id
        ORDER BY a.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$produkte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each product, fetch interested parties (Anfragen)
foreach ($produkte as &$produkt) {
    $sql_interessenten = "SELECT b.vorname, b.nachname, b.email, an.erstellt_am
                          FROM anfragen an
                          JOIN benutzer b ON an.absender_id = b.bid
                          WHERE an.artikel_id = :artikel_id
                          ORDER BY an.erstellt_am DESC";
    $stmt_int = $pdo->prepare($sql_interessenten);
    $stmt_int->execute(['artikel_id' => $produkt['id']]);
    $produkt['interessenten'] = $stmt_int->fetchAll(PDO::FETCH_ASSOC);
}
unset($produkt);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Artikel | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">

        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <span class="section-label">Verwaltung</span>
                <h1>Meine Artikel</h1>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-image">Bild</th>
                            <th>Titel</th>
                            <th>Kategorie</th>
                            <th>Preis</th>
                            <th>Interessenten</th>
                            <th class="text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produkte) === 0): ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    Du hast noch keine Artikel eingestellt.
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
                                <td>
                                    <?php if (count($produkt['interessenten']) > 0): ?>
                                        <select class="form-select" style="width: 100%; max-width: 250px;">
                                            <option selected disabled>Interessenten (<?= count($produkt['interessenten']) ?>)</option>
                                            <?php foreach ($produkt['interessenten'] as $int): ?>
                                                <option>
                                                    <?= htmlspecialchars($int['vorname'] . ' ' . $int['nachname']) ?> 
                                                    (<?= date('d.m.Y', strtotime($int['erstellt_am'])) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.9rem;">Noch keine Interessenten</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="button-stack" style="gap: 0.5rem; justify-content: flex-end;">
                                        <a href="produkt_detail.php?id=<?= $produkt['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem; width: auto;">
                                            Details
                                        </a>
                                        <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; width: auto;">
                                            Bearbeiten
                                        </a>
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
