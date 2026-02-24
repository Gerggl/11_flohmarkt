<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['anfrage_id'])) {
    $anfrage_id = (int)$_GET['anfrage_id'];
    $sql_update = "UPDATE interessenten an
                   JOIN artikel ar ON an.artikel_id = ar.id
                   SET an.status = 'gelesen'
                   WHERE an.inter_id = :anfrage_id AND ar.bid = :user_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute(['anfrage_id' => $anfrage_id, 'user_id' => $user_id]);
    header("Location: dashboard.php");
    exit;
}

$stmt_stats1 = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE bid = :user_id");
$stmt_stats1->execute(['user_id' => $user_id]);
$total_products = $stmt_stats1->fetchColumn();

$stmt_stats2 = $pdo->prepare("SELECT COUNT(*) FROM interessenten an JOIN artikel ar ON an.artikel_id = ar.id WHERE ar.bid = :user_id");
$stmt_stats2->execute(['user_id' => $user_id]);
$total_requests_count = $stmt_stats2->fetchColumn();

$stmt_stats_new = $pdo->prepare("SELECT COUNT(*) FROM interessenten an JOIN artikel ar ON an.artikel_id = ar.id WHERE ar.bid = :user_id AND an.status = 'neu'");
$stmt_stats_new->execute(['user_id' => $user_id]);
$new_requests_count = $stmt_stats_new->fetchColumn();

$sql_requests = "SELECT an.*, ar.titel, ar.bild_pfad, ar.preis, b.vorname, b.nachname, b.email
                 FROM interessenten an
                 JOIN artikel ar ON an.artikel_id = ar.id
                 JOIN benutzer b ON an.benutzer_id = b.bid
                 WHERE ar.bid = :user_id
                 ORDER BY an.zeitpunkt DESC";
$stmt_requests = $pdo->prepare($sql_requests);
$stmt_requests->execute(['user_id' => $user_id]);
$requests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            
            <section class="hero-section">
                <div style="max-width: 600px;">
                    <p style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 1rem;">Persönlicher Bereich</p>
                    <h1 style="font-size: 2.5rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1.1;">Guten Tag,<br><?= htmlspecialchars($_SESSION['vorname']) ?></h1>
                    <p style="margin-top: 1.5rem; color: var(--text-muted); font-size: 1.1rem; line-height: 1.6;">
                        Hier haben Sie den Überblick über Ihre Inserate und die aktuellen Anfragen Ihrer Interessenten.
                    </p>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <span class="hero-stat-label">Ihre Inserate</span>
                        <span class="hero-stat-value"><?= $total_products ?></span>
                    </div>
                    <div class="hero-stat-item">
                        <span class="hero-stat-label">Anfragen gesamt</span>
                        <span class="hero-stat-value" style="color: <?= $total_requests_count > 0 ? 'var(--primary)' : 'var(--text-muted)' ?>;"><?= $total_requests_count ?></span>
                    </div>
                </div>
            </section>

            <div class="dash-grid">
                
                <div class="dash-main">
                    <div class="card-title">
                        <span>Aktuelle Anfragen</span>
                        <?php if ($new_requests_count > 0): ?>
                            <span class="badge-neu"><?= $new_requests_count ?> Neu</span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($requests)): ?>
                        <div class="dashboard-card" style="text-align: center; padding: 4rem 2rem;">
                            <p style="color: var(--text-muted);">Noch keine Anfragen vorhanden.</p>
                            <a href="produkt_erstellen.php" class="btn btn-secondary mt-4">Neues Inserat erstellen</a>
                        </div>
                    <?php else: ?>
                        <div class="requests-container">
                            <?php foreach ($requests as $req): ?>
                                <div class="req-card <?= $req['status'] === 'neu' ? 'neu' : '' ?>">
                                    <?php if ($req['bild_pfad']): ?>
                                        <img src="<?= htmlspecialchars($req['bild_pfad']) ?>" class="req-img-small" alt="Produkt">
                                    <?php else: ?>
                                        <div class="req-img-small" style="background: var(--bg-main);"></div>
                                    <?php endif; ?>

                                    <div class="req-body">
                                        <span class="req-name"><?= htmlspecialchars($req['vorname'] . ' ' . $req['nachname']) ?></span>
                                        <div class="req-meta">
                                            Interessiert an: <span style="color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($req['titel']) ?></span>
                                            <br>
                                            Am <?= date('d.m.Y', strtotime($req['zeitpunkt'])) ?> um <?= date('H:i', strtotime($req['zeitpunkt'])) ?> Uhr
                                        </div>
                                    </div>

                                    <div class="req-price">
                                        <?= number_format($req['preis'], 2, ',', '.') ?> €
                                    </div>

                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <?php if ($req['status'] === 'neu'): ?>
                                            <a href="dashboard.php?action=read&anfrage_id=<?= $req['inter_id'] ?>" class="btn btn-secondary" style="padding: 0.5rem; width: 40px; height: 40px;" title="Gelesen">✓</a>
                                        <?php endif; ?>
                                        <a href="produkt_detail.php?id=<?= $req['artikel_id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Ansehen</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="dash-side">
                    <div class="dashboard-card">
                        <h3 style="font-size: 1rem; font-weight: 800; margin-bottom: 1rem;">Schnellzugriff</h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="produkt_erstellen.php" class="btn btn-primary w-full" style="justify-content: flex-start;">Inserat einstellen</a>
                            <a href="meine_artikel.php" class="btn btn-secondary w-full" style="justify-content: flex-start;">Alle Artikel verwalten</a>
                            <a href="produkt_liste.php" class="btn btn-secondary w-full" style="justify-content: flex-start;">Zum Marktplatz</a>
                        </div>
                    </div>

                    <div class="dashboard-card" style="margin-top: 2rem; background: var(--bg-main); border-style: dashed;">
                        <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">System Status</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">
                            Ihr Konto ist aktiv. Alle Funktionen stehen Ihnen uneingeschränkt zur Verfügung.
                        </p>
                    </div>
                </div>

            </div>

        </main>
    </div>
</body>
</html>
