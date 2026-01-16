<?php
// Esercizio: Sistema di login e autenticazione
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  
  // Credenziali di esempio (in produzione usare database)
  $utenti = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'user' => password_hash('user123', PASSWORD_DEFAULT)
  ];
  
  if (isset($utenti[$username]) && password_verify($password, $utenti[$username])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
    header('Location: login.php');
    exit;
  } else {
    $errore = 'Credenziali non valide';
  }
}

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .home-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: white;
      color: #667eea;
      border: none;
      padding: 12px 16px;
      border-radius: 50%;
      font-size: 24px;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    .home-btn:hover {
      transform: scale(1.1);
    }
    .login-container {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }
    h1 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      color: #333;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      background: #667eea;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background 0.3s;
    }
    button:hover {
      background: #764ba2;
    }
    .errore {
      background: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .successo {
      text-align: center;
      background: #d4edda;
      color: #155724;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .credenziali {
      background: #e7f3ff;
      padding: 15px;
      border-radius: 5px;
      margin-top: 20px;
      font-size: 13px;
    }
    .logout-btn {
      background: #e74c3c;
      margin-top: 20px;
    }
    .logout-btn:hover {
      background: #c0392b;
    }
  </style>
</head>
<body>

<a href="informatica.php" class="home-btn" title="Indietro">⬅️</a>

<div class="login-container">
  <h1>🔐 Login</h1>
  
  <?php if (isset($_SESSION['logged_in'])): ?>
    <div class="successo">
      ✅ Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
    </div>
    <a href="?logout=1"><button class="logout-btn">Logout</button></a>
  <?php else: ?>
    <?php if (isset($errore)): ?>
      <div class="errore">❌ <?php echo $errore; ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit">Accedi</button>
    </form>
    
    <div class="credenziali">
      <strong>📝 Credenziali di test:</strong><br>
      Username: <code>admin</code> Password: <code>admin123</code><br>
      Username: <code>user</code> Password: <code>user123</code>
    </div>
  <?php endif; ?>
</div>

</body>
</html>