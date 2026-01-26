<?php
declare(strict_types=1);
require_once __DIR__ . "/database.php";

session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = (string)($_POST["password"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email non valida.";
    } else {
        $stmt = $mysqli->prepare("
            SELECT ID_utente, Email, Tipo_utente, Password_hash
            FROM Utenti
            WHERE Email = ?
            LIMIT 1
        ");
        if (!$stmt) {
            $errors[] = "Errore interno (prepare).";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if (!$user || !password_verify($password, $user["Password_hash"])) {
                $errors[] = "Credenziali non corrette.";
            } else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int)$user["ID_utente"];
                $_SESSION["email"] = $user["Email"];
                $_SESSION["tipo_utente"] = $user["Tipo_utente"];

                header("Location: dashboard.php"); // cambia se vuoi
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;margin:40px;max-width:520px}
    label{display:block;margin-top:12px}
    input{width:100%;padding:10px;margin-top:6px}
    button{margin-top:16px;padding:10px 14px;cursor:pointer}
    .err{background:#ffecec;border:1px solid #f5a5a5;padding:10px;border-radius:8px;margin:12px 0}
    a{display:inline-block;margin-top:14px}
  </style>
</head>
<body>

<h1>Login</h1>

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

  <label>Password</label>
  <input type="password" name="password" required>

  <button type="submit">Entra</button>
</form>

<a href="registrazione.php">Crea un account</a>

</body>
</html>