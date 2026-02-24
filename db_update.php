<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM artikel LIKE 'bid'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $pdo->exec("ALTER TABLE artikel ADD COLUMN bid INT(11) DEFAULT NULL");
        echo "Column 'bid' added to 'artikel' table successfully.";
    } else {
        echo "Column 'bid' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
