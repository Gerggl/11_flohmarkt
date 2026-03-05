<?php
$message = "";

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
        require_once("function.php");

        if ($passwort != $passwort_zwei) {
            $message = "Passwörter sind nicht gleich!";
        } elseif (!istPasswortSicher($passwort)) {
            $message = "Ungültiges Passwort! Bitte geben Sie ein anderes ein!";
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

                header("Location: login.php");
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "Email ist bereits im System!";
                } else {
                    $message = "Fehler bei der Registrierung.";
                }
            }
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
    <title>Konto erstellen</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 1000px;">
            <div class="auth-sidebar">
                <h2>Konto erstellen</h2>
                <p>Werden Sie Teil unserer Community und entdecken Sie tolle Inserate in Ihrer Nähe.</p>
            </div>

            <div class="auth-content">
                <form action="" method="post">
                    <div class="form-header">
                        <div class="brand-logo mb-4">
                            <img src="bilder/logo.png" alt="Logo">
                            <span>Flohmarkt</span>
                        </div>
                        <h1>Registrieren</h1>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="vname">Vorname</label>
                            <input type="text" name="vorname" id="vname" class="input-control" required placeholder="Gerd">
                        </div>

                        <div class="form-group">
                            <label for="nname">Nachname</label>
                            <input type="text" name="nachname" id="nname" class="input-control" required placeholder="Mueller">
                        </div>

                        <div class="form-group full-col">
                            <label for="mail">E-Mail</label>
                            <input type="email" name="email" id="mail" class="input-control" required placeholder="ihre@email.de">
                        </div>

                        <div class="form-group">
                            <label for="geb">Geburtstag</label>
                            <input type="date" name="geburtsdatum" id="geb" class="input-control" required>
                        </div>

                        <div class="form-group">
                            <label for="sex">Geschlecht</label>
                            <select name="geschlecht" id="sex" class="input-control" required>
                                <option value="m">Männlich</option>
                                <option value="w">Weiblich</option>
                                <option value="d">Divers</option>
                            </select>
                        </div>

                        <div class="form-group full-col">
                            <label for="str">Anschrift</label>
                            <input type="text" name="strasse" id="str" class="input-control" required placeholder="Straße, Nr.">
                        </div>

                        <div class="form-group">
                            <label for="plz">PLZ</label>
                            <input type="text" name="plz" id="plz" class="input-control" required placeholder="12345">
                        </div>

                        <div class="form-group">
                            <label for="ort">Ort</label>
                            <input type="text" name="ort" id="ort" class="input-control" required placeholder="Stadt">
                        </div>

                        <div class="form-group full-col">
                            <label for="tel">Mobilnummer</label>
                            <input type="tel" name="telefon" id="tel" class="input-control" required placeholder="+49 ...">
                        </div>

                        <div class="form-group">
                            <label for="pw1">Passwort</label>
                            <input type="password" name="passwort" id="pw1" class="input-control" required placeholder="••••••••">
                        </div>

                        <div class="form-group">
                            <label for="pw2">Wiederholen</label>
                            <input type="password" name="passwortzwei" id="pw2" class="input-control" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-size: 0.9rem; color: var(--text-muted);">
                            <input type="checkbox" name="agb" required style="width: 1.1rem; height: 1.1rem; accent-color: var(--primary);">
                            <span>Ich akzeptiere die Nutzungsbedingungen und AGB.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Registrieren</button>

                    <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted); text-align: center;">
                        Abbrechen? <a href="login.php" style="color: var(--primary); font-weight: 600;">Zum Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>

</html>