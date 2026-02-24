<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$neu_anfragen_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $sql_count = "SELECT COUNT(*) FROM anfragen an
                      JOIN artikel ar ON an.artikel_id = ar.id
                      WHERE ar.bid = :user_id AND an.status = 'neu'";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute(['user_id' => $_SESSION['user_id']]);
        $neu_anfragen_count = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        // Falls die Tabelle fehlt (SQLSTATE 42S02), versuchen wir sie automatisch zu erstellen
        if ($e->getCode() == '42S02') {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS anfragen (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    artikel_id INT NOT NULL,
                    absender_id INT NOT NULL,
                    nachricht TEXT,
                    status ENUM('neu', 'gelesen') DEFAULT 'neu',
                    erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (artikel_id),
                    INDEX (absender_id)
                )");
                // Nach der Erstellung ist der Count natürlich 0
                $neu_anfragen_count = 0;
            } catch (PDOException $e2) {
                // Falls auch das fehlschlägt, geben wir auf und setzen 0
                $neu_anfragen_count = 0;
            }
        } else {
            $neu_anfragen_count = 0;
        }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar">
    <div class="brand-logo brand-dark mb-12">
        <img src="bilder/logo.png" alt="Logo">
        <span>Flohmarkt</span>
    </div>
    
    <nav>
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="meine_artikel.php" class="nav-link <?= $current_page == 'meine_artikel.php' ? 'active' : '' ?>">Meine Artikel</a>
        <a href="produkt_liste.php" class="nav-link <?= $current_page == 'produkt_liste.php' ? 'active' : '' ?>">Kollektion</a>
        <a href="produkt_erstellen.php" class="nav-link <?= $current_page == 'produkt_erstellen.php' ? 'active' : '' ?>">Neues Inserat</a>
        <a href="abmelden.php" class="nav-link">Abmelden</a>
    </nav>
</aside>
