<?php
$message = "";

// Prüfen, ob der Button geklickt wurde
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vorname = trim($_POST["vorname"]);
    $nachname = trim($_POST["nachname"]);
    $geburtsdatum = trim($_POST["geburtsdatum"]);
    $geschlecht = trim($_POST["geschlecht"]);
    $email = trim($_POST["email"]);
    $strasse = trim($_POST["strasse"]);
    $plz = trim($_POST["plz"]);
    $ort = trim($_POST["ort"]);
    $telefon = trim($_POST["telefon"]);
    $passwort = trim($_POST["passwort"]);
    $passwort_zwei = trim($_POST["passwortzwei"]);
    $agb = isset($_POST["agb"]) ? 1 : 0;

    // Serverseitige Validierung
    if (
        !empty($vorname) &&
        !empty($nachname) &&
        !empty($geburtsdatum) &&
        !empty($geschlecht) &&
        !empty($email) &&
        !empty($strasse) &&
        !empty($plz) &&
        !empty($ort) &&
        !empty($passwort) &&
        !empty($passwort_zwei) &&
        $agb == 1
    ) {
        // Prüfen, ob Passwort passt
        if ($passwort != $passwort_zwei) {
            echo "Paswörter sind nicht gleich!";
        } else {
            $passwordHash = password_hash($passwort, PASSWORD_DEFAULT);

            require_once("db.php");

            try {
                $sql = "INSERT INTO benutzer 
            (vorname, nachname, geburtsdatum, geschlecht, email, strasse, plz, ort, telefon, passwort, agb) 
            VALUES 
            (:vorname, :nachname, :geburtsdatum, :geschlecht, :email, :strasse, :plz, :ort, :telefon, :passwort, :agb)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                    ":vorname" => $vorname,
                    ":nachname" => $nachname,
                    ":geburtsdatum" => $geburtsdatum,
                    ":geschlecht" => $geschlecht,
                    ":email" => $email,
                    ":strasse" => $strasse,
                    ":plz" => $plz,
                    ":ort" => $ort,
                    ":telefon" => $telefon,
                    ":passwort" => $passwordHash,
                    ":agb" => $agb
                ));

                $message = "Registrierung erfolgreich! <a href=\"login.php\">Zum Login</a>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "Email ist bereits im System!";
                } else {
                    $message = "Es ist ein Fehler beim Registrieren aufgetreten: " . $e->getMessage();
                }
            }
            header("Location:login.php");
        }
    } else {
        $message = "Bitte alle Pflichtfelder ausfüllen und die AGB akzeptieren!";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Registrierung | Flohmarkt</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card wide">
            
            <div class="auth-sidebar">
                <div class="brand-section">
                    <div class="brand-logo">
                        <img src="bilder/logo.png" alt="Logo">
                        <span>Flohmarkt</span>
                    </div>
                    <h2>Willkommen im Club.</h2>
                    <p class="text-white text-opacity-90">Schätze finden, Nachhaltigkeit leben. Schön, dass du Teil unserer Community wirst!</p>
                </div>

                <ul class="benefits-list">
                    <li>Persönliche Daten</li>
                    <li>Anschrift & Kontakt</li>
                    <li>Sicherheit</li>
                </ul>

                <div class="sidebar-footer">
                    <p class="text-opacity-70 text-sm">© 2026 Flohmarkt Community<br>Sicher & verschlüsselt</p>
                </div>
            </div>

            <div class="auth-content">
                <form action="" method="post">
                    <div class="form-header">
                        <h1>Konto erstellen</h1>
                        <p class="text-sub">In nur 2 Minuten zum Marktplatz-Profi.</p>
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-error"><?= $message ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <div class="floating-label">
                                <input type="text" name="vorname" id="vname" class="input-control" placeholder=" " required>
                                <label for="vname">Vorname</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="text" name="nachname" id="nname" class="input-control" placeholder=" " required>
                                <label for="nname">Nachname</label>
                            </div>
                        </div>

                        <div class="form-group full">
                            <div class="floating-label">
                                <input type="email" name="email" id="mail" class="input-control" placeholder=" " required>
                                <label for="mail">E-Mail Adresse</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="date" name="geburtsdatum" id="geb" class="input-control" placeholder=" " required>
                                <label for="geb">Geburtsdatum</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <select name="geschlecht" id="sex" class="input-control" required>
                                    <option value="" disabled selected hidden></option>
                                    <option value="m">Männlich</option>
                                    <option value="w">Weiblich</option>
                                    <option value="d">Divers</option>
                                </select>
                                <label for="sex">Geschlecht</label>
                            </div>
                        </div>

                        <div class="form-group full">
                            <div class="floating-label">
                                <input type="text" name="strasse" id="str" class="input-control" placeholder=" " required>
                                <label for="str">Straße & Hausnummer</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="text" name="plz" id="plz" class="input-control" placeholder=" " required>
                                <label for="plz">PLZ</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="text" name="ort" id="ort" class="input-control" placeholder=" " required>
                                <label for="ort">Ort</label>
                            </div>
                        </div>
                        
                        <div class="form-group full">
                             <div class="floating-label">
                                 <input type="tel" name="telefon" id="tel" class="input-control" placeholder=" " required>
                                 <label for="tel">Telefon</label>
                             </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="password" name="passwort" id="pw1" class="input-control" placeholder=" " required>
                                <label for="pw1">Passwort</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="floating-label">
                                <input type="password" name="passwortzwei" id="pw2" class="input-control" placeholder=" " required>
                                <label for="pw2">Passwort wiederholen</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label class="checkbox-field">
                            <input type="checkbox" name="agb" required>
                            <span>Ich akzeptiere die <a href="#">Nutzungsbedingungen</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Jetzt kostenlos registrieren</button>

                    <div class="footer-text">
                        Schon einen Account? <a href="login.php">Hier einloggen</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>
