<?php
session_start();

if (!isset($_SESSION['eingeloggt'])) {
    header('Location: Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artikel_id'])) {
    require_once 'db.php';
    
    $artikel_id = $_POST['artikel_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $check_sql = "SELECT bid, bild_pfad FROM artikel WHERE id = :id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(['id' => $artikel_id]);
        $artikel = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($artikel && $artikel['bid'] == $user_id) {
            $del_int = "DELETE FROM interessenten WHERE artikel_id = :id";
            $pdo->prepare($del_int)->execute(['id' => $artikel_id]);

            $del_art = "DELETE FROM artikel WHERE id = :id";
            $pdo->prepare($del_art)->execute(['id' => $artikel_id]);

            if (!empty($artikel['bild_pfad']) && file_exists($artikel['bild_pfad'])) {
                unlink($artikel['bild_pfad']);
            }

            header('Location: meine_artikel.php?status=deleted');
            exit;
        } else {
            header('Location: meine_artikel.php?status=error');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: meine_artikel.php?status=db_error');
        exit;
    }
} else {
    header('Location: meine_artikel.php');
    exit;
}

