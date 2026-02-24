<?php
$targetDir = "uploads/"; // / muss dabei stehen

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['meine_datei'])) { //name tag
    $error = $_FILES['meine_datei']['error'];

    //Prüfen, welcher Fehler aufgetreten ist
    if ($error === UPLOAD_ERR_NO_FILE) {
        echo "Keine neue Datei hochgeladen.";
    } else if ($error !== UPLOAD_ERR_OK) {
        echo "Es ist ein Fehler beim Upload aufgetreten. Fehlercode: " . $error;
    } else {
        //Alles ok, Upload verarbeiten

        //1. Schritt
        $file = $_FILES['meine_datei'];

        //2. Dateigröße prüfen (z.B. 2 MB) => genau 1024 * 1024
        if ($file['size'] > 2000000) { //$_FILES['meine_datei']['size']
            die("Die hochgeladene Datei ist zu groß.");
        }

        //3. echten MIME-Type überprüfen (SICHERHEIT!!)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($mimeType, $allowTypes)) {
            die("Ungültiger Dateityp.");
        }

        //4. Dateiendung ermitteln um neuen Namen generieren
        //verhindert das Übrschreiben und Ausführen von bösen Skripten
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION); //Filtern der Dateiendung aus dem Dateinamen
        $newName = uniqid('upload_', true) . "." . $extension; //Dateiname + . + Dateiendung
        $destination = $targetDir . $newName;


        //5. Datei vom temp-Ordner ins Zeilverzeichnis verschieben
        if (move_uploaded_file($file["tmp_name"], $destination)) {
            echo ("Datei-Upload erfolgreich ausgeführt.");
        } else {
            echo "Fehler beim Verschieben der Datei.";
        }
    }
}
