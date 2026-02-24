<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$neu_anfragen_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $sql_count = "SELECT COUNT(*) FROM interessenten an
                      JOIN artikel ar ON an.artikel_id = ar.id
                      WHERE ar.bid = :user_id";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute(['user_id' => $_SESSION['user_id']]);
        $neu_anfragen_count = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        $neu_anfragen_count = 0;
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar">
    <div class="brand-section">
        <div class="brand-logo">
            <img src="bilder/logo.png" alt="Logo">
            <span>Flohmarkt</span>
        </div>
    </div>
    
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
            <?php if ($neu_anfragen_count > 0): ?>
                <span class="nav-badge"><?= $neu_anfragen_count ?></span>
            <?php endif; ?>
        </a>
        <a href="meine_artikel.php" class="nav-link <?= $current_page == 'meine_artikel.php' ? 'active' : '' ?>">Meine Artikel</a>
        <a href="produkt_liste.php" class="nav-link <?= $current_page == 'produkt_liste.php' ? 'active' : '' ?>">Kollektion</a>
        <a href="produkt_erstellen.php" class="nav-link <?= $current_page == 'produkt_erstellen.php' ? 'active' : '' ?>">Einstellen</a>
        <a href="abmelden.php" class="nav-link" style="margin-top: auto; color: var(--text-muted);">Abmelden</a>
    </nav>
</aside>
