<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// 1. Mark as read logic
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['anfrage_id'])) {
    $anfrage_id = (int)$_GET['anfrage_id'];
    // Verify ownership via join
    $sql_update = "UPDATE anfragen an
                   JOIN artikel ar ON an.artikel_id = ar.id
                   SET an.status = 'gelesen'
                   WHERE an.id = :anfrage_id AND ar.bid = :user_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute(['anfrage_id' => $anfrage_id, 'user_id' => $user_id]);
    header("Location: dashboard.php");
    exit;
}

// 2. Fetch Stats
// Total Products
$stmt_stats1 = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE bid = :user_id");
$stmt_stats1->execute(['user_id' => $user_id]);
$total_products = $stmt_stats1->fetchColumn();

// New Requests
$stmt_stats2 = $pdo->prepare("SELECT COUNT(*) FROM anfragen an JOIN artikel ar ON an.artikel_id = ar.id WHERE ar.bid = :user_id AND an.status = 'neu'");
$stmt_stats2->execute(['user_id' => $user_id]);
$new_requests_count = $stmt_stats2->fetchColumn();

// 3. Fetch Requests
$sql_requests = "SELECT an.*, ar.titel, ar.bild_pfad, ar.preis, b.vorname, b.nachname, b.email
                 FROM anfragen an
                 JOIN artikel ar ON an.artikel_id = ar.id
                 JOIN benutzer b ON an.absender_id = b.bid
                 WHERE ar.bid = :user_id
                 ORDER BY an.erstellt_am DESC";
$stmt_requests = $pdo->prepare($sql_requests);
$stmt_requests->execute(['user_id' => $user_id]);
$requests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Flohmarkt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            display: block;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .request-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }
        .request-item.neu {
            border-left: 4px solid #fbbf24;
            background: rgba(251, 191, 36, 0.05);
        }
        .request-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.06);
        }
        .req-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }
        .req-info {
            flex: 1;
        }
        .req-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .req-sender {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .req-date {
            font-size: 0.8rem;
            opacity: 0.5;
        }
        .badge-neu {
            background: #fbbf24;
            color: #000;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="app-layout">
        <?php include 'sidebar.php'; ?>

        <main class="app-main">
            <div class="form-header" style="margin-bottom: 2rem;">
                <span class="section-label">Übersicht</span>
                <h1>Hallo, <?= htmlspecialchars($_SESSION['vorname']) ?>!</h1>
                <p style="font-size: 1.1rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <?php if ($new_requests_count > 0): ?>
                        🔔 Du hast <strong><?= $new_requests_count ?></strong> neue <?= $new_requests_count == 1 ? 'Anfrage' : 'Anfragen' ?> zu deinem Produkt erhalten!
                    <?php else: ?>
                        Keine neue Anfragen.
                    <?php endif; ?>
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-value"><?= $total_products ?></span>
                    <span class="stat-label">Produkte</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= $new_requests_count ?></span>
                    <span class="stat-label">Neue Anfragen</span>
                </div>
            </div>

            <div class="section-header mb-8">
                <h2>Aktuelle Anfragen</h2>
            </div>

            <?php if (empty($requests)): ?>
                <div class="glass-panel p-8 text-center text-muted">
                    <p>Du hast noch keine Anfragen erhalten.</p>
                </div>
            <?php else: ?>
                <div class="requests-list">
                    <?php foreach ($requests as $req): ?>
                        <div class="request-item <?= $req['status'] === 'neu' ? 'neu' : '' ?>">
                            <?php if ($req['bild_pfad']): ?>
                                <img src="<?= htmlspecialchars($req['bild_pfad']) ?>" class="req-img" alt="Produkt">
                            <?php else: ?>
                                <div class="req-img" style="background: #334155; display: flex; align-items: center; justify-content: center; font-size: 2rem;">📦</div>
                            <?php endif; ?>
                            
                            <div class="req-info">
                                <div class="req-title">
                                    <?= htmlspecialchars($req['titel']) ?> 
                                    <?php if ($req['status'] === 'neu'): ?>
                                        <span class="badge-neu">Neu</span>
                                    <?php endif; ?>
                                </div>
                                <div class="req-sender">
                                    Von: <strong><?= htmlspecialchars($req['vorname'] . ' ' . $req['nachname']) ?></strong> 
                                    (<a href="mailto:<?= htmlspecialchars($req['email']) ?>" style="color: var(--primary);"><?= htmlspecialchars($req['email']) ?></a>)
                                </div>
                                <div class="req-date"><?= date('d.m.Y H:i', strtotime($req['erstellt_am'])) ?> Uhr</div>
                            </div>

                            <div class="req-actions">
                                <?php if ($req['status'] === 'neu'): ?>
                                    <a href="dashboard.php?action=read&anfrage_id=<?= $req['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.7rem;">Gelesen</a>
                                <?php endif; ?>
                                <a href="produkt_detail.php?id=<?= $req['artikel_id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.7rem;">Produkt</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
