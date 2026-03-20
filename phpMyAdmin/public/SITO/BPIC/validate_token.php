<?php
declare(strict_types=1);
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/api/jwt.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /SITO/BPIC/login.php');
    exit;
}

$token = trim($_POST['token'] ?? '');
if ($token === '') {
    $error = 'Token mancante.';
    echo "<p>$error</p><p><a href=\"/SITO/BPIC/login.php\">Torna al login</a></p>";
    exit;
}

$payload = verify_jwt($token, JWT_SECRET);
if (!$payload || empty($payload['user_id'])) {
    $error = 'Token non valido o scaduto.';
    echo "<p>$error</p><p><a href=\"/SITO/BPIC/login.php\">Torna al login</a></p>";
    exit;
}

$userId = (int)$payload['user_id'];

try {
    $stmt = $pdo->prepare('SELECT ID_utente, Email FROM Utenti WHERE ID_utente = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    echo "<p>Errore interno (execute).</p><p><a href=\"/SITO/BPIC/login.php\">Torna al login</a></p>";
    exit;
}

if (!$user) {
    echo "<p>Utente non trovato.</p><p><a href=\"/SITO/BPIC/login.php\">Torna al login</a></p>";
    exit;
}

// Imposta la sessione come se l'utente avesse fatto login
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['ID_utente'];
$_SESSION['email'] = $user['Email'];
$_SESSION['tipo_utente'] = $user['Tipo_utente'] ?? null;

// Recupera ruoli e privilegi per mostrare nella dashboard
$stmt = $pdo->prepare('SELECT r.ID_ruolo, r.Nome_ruolo, p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
    FROM Utente_Ruolo ur
    JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
    JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
    JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
    WHERE ur.email_utente = ?');
$email = $user['Email'];
$stmt->execute([$email]);
$result = $stmt->fetchAll();

$roles = [];
$permissions = [];
$roleMap = [];
$permMap = [];

foreach ($result as $row) {
    $roleId = (int)$row['ID_ruolo'];
    if (!isset($roleMap[$roleId])) {
        $roleMap[$roleId] = true;
        $roles[] = ['id' => $roleId, 'name' => $row['Nome_ruolo']];
    }

    $permId = (int)$row['ID_privilegio'];
    if (!isset($permMap[$permId])) {
        $permMap[$permId] = true;
        $permissions[] = ['id' => $permId, 'name' => $row['Nome_privilegio'], 'resource' => $row['Risorsa'], 'action' => $row['Azione']];
    }
}

$_SESSION['roles'] = $roles;
$_SESSION['permissions'] = $permissions;

// Reindirizza al profilo contratto
header('Location: /SITO/BPIC/Profilo_contratto.php');
exit;
