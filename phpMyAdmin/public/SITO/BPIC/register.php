<?php
declare(strict_types=1);
require_once __DIR__ . "/database.php";

$errors = [];
$ok = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $password = (string)($_POST["password"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email non valida.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password troppo corta (minimo 8 caratteri).";
    }

    if (!$errors) {
        // Controllo email già presente
        $stmt = $mysqli->prepare("SELECT ID_utente FROM Utenti WHERE Email = ? LIMIT 1");
        if (!$stmt) {
            $errors[] = "Errore interno (prepare).";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = $res && $res->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $errors[] = "Email già registrata.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                // Transaction (così profilo + utente vengono creati insieme)
                $mysqli->begin_transaction();

                try {
                    // 1) Creo profilo minimo (necessario per ID_profilo NOT NULL)
                    $livello = "base";
                    $mese = 1; // valore valido (check 1..12)
                    $nullMaggiorazioni = null;

                    $stmt = $mysqli->prepare("
                        INSERT INTO Profilo_contratto (Maggiorazioni, Livello_dipendente, Mese_lavorativo)
                        VALUES (?, ?, ?)
                    ");
                    if (!$stmt) {
                        throw new Exception("Errore prepare profilo.");
                    }

                    // bind_param non accetta direttamente NULL con tipi stretti: uso variabile
                    $stmt->bind_param("dsi", $nullMaggiorazioni, $livello, $mese);
                    // Nota: con "d" e NULL MySQLi può warning; alternativa:
                    // se ti dà problemi, cambia Maggiorazioni a 0.0 oppure usa bind_param("ssi") e cast.
                    $stmt->execute();
                    $stmt->close();

                    $idProfilo = $mysqli->insert_id;

                    // 2) Inserisco utente
                    $tipo = "non_abbonato";
                    $idBusta = null;

                    $stmt = $mysqli->prepare("
                        INSERT INTO Utenti (N_Telefono, Email, Tipo_utente, ID_profilo, ID_busta, Password_hash)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    if (!$stmt) {
                        throw new Exception("Errore prepare utente.");
                    }

                    // per i NULL in MySQLi: uso variabili e poi setto a NULL/valore
                    $telefonoParam = ($telefono !== "") ? $telefono : null;

                    $stmt->bind_param(
                        "sssiss",
                        $telefonoParam,
                        $email,
                        $tipo,
                        $idProfilo,
                        $idBusta,       // NULL ok
                        $passwordHash
                    );
                    $stmt->execute();
                    $stmt->close();

                    $mysqli->commit();
                    $ok = true;
                } catch (Throwable $e) {
                    $mysqli->rollback();
                    $errors[] = "Errore durante la registrazione.";
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Registrazione</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;margin:40px;max-width:520px}
    label{display:block;margin-top:12px}
    input{width:100%;padding:10px;margin-top:6px}
    button{margin-top:16px;padding:10px 14px;cursor:pointer}
    .err{background:#ffecec;border:1px solid #f5a5a5;padding:10px;border-radius:8px;margin:12px 0}
    .ok{background:#eaffea;border:1px solid #9ee49e;padding:10px;border-radius:8px;margin:12px 0}
    a{display:inline-block;margin-top:14px}
  </style>
</head>
<body>

<h1>Registrazione</h1>

<?php if ($ok): ?>
  <div class="ok">Registrazione completata! Ora puoi fare il login.</div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="err">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" autocomplete="on">
  <label>Email</label>
  <input type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">

  <label>Telefono (opzionale)</label>
  <input type="text" name="telefono" value="<?= htmlspecialchars($_POST["telefono"] ?? "", ENT_QUOTES, "UTF-8") ?>">

  <label>Password</label>
  <input type="password" name="password" required minlength="8">

  <button type="submit">Registrati</button>
</form>

<a href="login.php">Vai al login</a>

</body>
</html>