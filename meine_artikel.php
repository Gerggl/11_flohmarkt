<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT a.id, a.titel, a.preis, a.bild_pfad, a.gekauft_von, k.bezeichnung AS kategorie, b.vorname AS k_vorname, b.nachname AS k_nachname
        FROM artikel a
        LEFT JOIN kategorie k ON a.kid = k.kid
        LEFT JOIN benutzer b ON a.gekauft_von = b.bid
        WHERE a.bid = :user_id
        ORDER BY a.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$produkte = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($produkte as &$produkt) {
    $sql_interessenten = "SELECT b.bid, b.vorname, b.nachname, b.email, an.zeitpunkt
                          FROM interessenten an
                          JOIN benutzer b ON an.benutzer_id = b.bid
                          WHERE an.artikel_id = :artikel_id
                          ORDER BY an.zeitpunkt DESC";
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
    <title>Meine Artikel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header">
                <p style="color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Verwaltung</p>
                <h1>Meine Artikel</h1>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    Der Artikel wurde erfolgreich als verkauft markiert.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem; border-color: var(--error); color: var(--error); background: #FDF2F2;">
                    Der Artikel wurde erfolgreich gelöscht.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && strpos($_GET['status'], 'error') !== false): ?>
                <div class="alert alert-error" style="margin-bottom: 2rem;">
                    Es ist ein Fehler beim Löschen aufgetreten.
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Vorschau</th>
                            <th>Bezeichnung</th>
                            <th>Kategorie</th>
                            <th>Preis</th>
                            <th style="width: 250px;" class="text-center">Anfragen</th>
                            <th class="text-center">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produkte)): ?>
                            <tr>
                                <td colspan="6" style="padding: 4rem; text-align: center; color: var(--text-muted);">
                                    Sie haben noch keine Produkte zum Verkauf eingestellt.
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
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">ID: #<?= $produkt['id'] ?></div>
                                </td>
                                <td><span class="cat-pill"><?= htmlspecialchars($produkt['kategorie'] ?? 'Sonstiges') ?></span></td>
                                <td style="font-weight: 600; color: var(--primary);"><?= number_format($produkt['preis'], 2, ',', '.') ?> €</td>
                                <td class="text-center">
                                    <?php if ($produkt['gekauft_von']): ?>
                                        <div style="background: rgba(34, 197, 94, 0.1); color: #16a34a; padding: 0.5rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-align: center; border: 1px solid rgba(34, 197, 94, 0.2);">
                                            Verkauft an:<br>
                                            <?= htmlspecialchars($produkt['k_vorname'] . ' ' . $produkt['k_nachname']) ?>
                                        </div>
                                    <?php elseif (count($produkt['interessenten']) > 0): ?>
                                        <form action="verkauf_bestaetigung.php" method="GET">
                                            <input type="hidden" name="artikel_id" value="<?= $produkt['id'] ?>">
                                            <select name="kaeufer_id" class="interest-select" required onchange="this.form.submit()">
                                                <option selected disabled>Verkaufen an...</option>
                                                <?php foreach ($produkt['interessenten'] as $int): ?>
                                                    <option value="<?= $int['bid'] ?>">
                                                        <?= htmlspecialchars($int['vorname'] . ' ' . $int['nachname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">Noch kein Interesse</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="produkt_detail.php?id=<?= $produkt['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; flex: 1;">Details</a>
                                        <a href="produkt_bearbeiten.php?id=<?= $produkt['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; flex: 1;">Bearbeiten</a>
                                        <form action="produkt_loeschen.php" method="POST" onsubmit="return confirm('Möchten Sie diesen Artikel wirklich unwiderruflich löschen?')" style="flex: 1;">
                                            <input type="hidden" name="artikel_id" value="<?= $produkt['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 0.5rem 1rem; font-size: 0.85rem; background: var(--error); color: white; flex: 1; height:40px">Löschen</button>
                                        </form>
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

