<?php
require_once 'db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS anfragen (
        id INT AUTO_INCREMENT PRIMARY KEY,
        artikel_id INT NOT NULL,
        absender_id INT NOT NULL,
        nachricht TEXT,
        status ENUM('neu', 'gelesen') DEFAULT 'neu',
        erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (artikel_id),
        INDEX (absender_id)
    )";
    
    $pdo->exec($sql);
    echo "Tabelle 'anfragen' erfolgreich erstellt oder bereits vorhanden.";
} catch (PDOException $e) {
    die("Fehler beim Erstellen der Tabelle: " . $e->getMessage());
}
?>
