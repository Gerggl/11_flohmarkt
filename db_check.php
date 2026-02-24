<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM artikel LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "Columns in 'artikel': " . implode(", ", array_keys($row));
    } else {
        $stmt = $pdo->query("DESCRIBE artikel");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columns in 'artikel' (from DESCRIBE): " . implode(", ", $columns);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
