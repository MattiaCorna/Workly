<?php
declare(strict_types=1);
require_once __DIR__ . "/database.php";

session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = (string)($_POST["password"] ?? "");
    $confirm_password = (string)($_POST["confirm_password"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $tipo_utente = 'non_abbonato'; // default

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email non valida.";
    }

    if (strlen($password) < 8) {
        $errors[] = "La password deve essere di almeno 8 caratteri.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Le password non coincidono.";
    }

    if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $telefono)) {
        $errors[] = "Numero di telefono non valido.";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Controlla se email già esistente
        $stmt = $mysqli->prepare("SELECT ID_utente FROM Utenti WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $errors[] = "Email già registrata.";
        } else {
            // Inserisci nuovo utente
            $stmt = $mysqli->prepare("INSERT INTO Utenti (Email, Password_hash, Tipo_utente, N_Telefono, ID_profilo) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $email, $password_hash, $tipo_utente, $telefono);
            if ($stmt->execute()) {
                header("Location: login.php?success=1");
                exit;
            } else {
                $errors[] = "Errore nella registrazione.";
            }
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Registrazione</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h1 class="h3 mb-0">Registrazione</h1>
          </div>
          <div class="card-body">
            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, "UTF-8") ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
              <div class="alert alert-success">Registrazione completata! Ora puoi fare il login.</div>
            <?php endif; ?>

            <form method="post" autocomplete="on">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">
              </div>
              <div class="mb-3">
                <label for="telefono" class="form-label">Numero di Telefono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" required value="<?= htmlspecialchars($_POST["telefono"] ?? "", ENT_QUOTES, "UTF-8") ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="mb-3">
                <label for="confirm_password" class="form-label">Conferma Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
              </div>
              <button type="submit" class="btn btn-primary">Registrati</button>
            </form>
            <a href="login.php" class="d-block mt-3">Hai già un account? Accedi</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"></script>
</body>
</html>