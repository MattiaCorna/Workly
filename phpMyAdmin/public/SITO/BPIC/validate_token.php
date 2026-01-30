<?php
declare(strict_types=1);
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/api/jwt.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$token = trim($_POST['token'] ?? '');
if ($token === '') {
    $error = 'Token mancante.';
    echo "<p>$error</p><p><a href=\"login.php\">Torna al login</a></p>";
    exit;
}

$payload = verify_jwt($token, JWT_SECRET);
if (!$payload || empty($payload['user_id'])) {
    $error = 'Token non valido o scaduto.';
    echo "<p>$error</p><p><a href=\"login.php\">Torna al login</a></p>";
    exit;
}

$userId = (int)$payload['user_id'];

$stmt = $mysqli->prepare('SELECT ID_utente, Email FROM Utenti WHERE ID_utente = ? LIMIT 1');
if (!$stmt) {
    echo "<p>Errore interno (prepare).</p><p><a href=\"login.php\">Torna al login</a></p>";
    exit;
}

$stmt->bind_param('i', $userId);
if (!$stmt->execute()) {
    echo "<p>Errore interno (execute).</p><p><a href=\"login.php\">Torna al login</a></p>";
    $stmt->close();
    exit;
}

$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    echo "<p>Utente non trovato.</p><p><a href=\"login.php\">Torna al login</a></p>";
    exit;
}

// Imposta la sessione come se l'utente avesse fatto login
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['ID_utente'];
$_SESSION['email'] = $user['Email'];
$_SESSION['tipo_utente'] = $user['Tipo_utente'] ?? null;

// Recupera ruoli e privilegi per mostrare nella dashboard
$stmt = $mysqli->prepare('SELECT r.ID_ruolo, r.Nome_ruolo, p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
    FROM Utente_Ruolo ur
    JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
    JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
    JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
    WHERE ur.email_utente = ?');
if ($stmt) {
    $email = $user['Email'];
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $roles = [];
    $permissions = [];
    $roleMap = [];
    $permMap = [];

    while ($row = $result->fetch_assoc()) {
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

    $stmt->close();

    $_SESSION['roles'] = $roles;
    $_SESSION['permissions'] = $permissions;
}

// Reindirizza alla dashboard
header('Location: dashboard.php');
exit;
