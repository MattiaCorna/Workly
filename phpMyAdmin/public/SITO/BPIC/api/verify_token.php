<?php
declare(strict_types=1);
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/jwt.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$token = trim($input['token'] ?? ($_POST['token'] ?? ''));
if ($token === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Token mancante.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = verify_jwt($token, JWT_SECRET);
if (!$payload || empty($payload['user_id'])) {
    http_response_code(200);
    echo json_encode(['valid' => false, 'error' => 'Token non valido o scaduto.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$payload['user_id'];

// Recupera email e ruoli/privilegi
$stmt = $mysqli->prepare('SELECT Email FROM Utenti WHERE ID_utente = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno (prepare).'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('i', $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno (execute).'], JSON_UNESCAPED_UNICODE);
    exit;
}
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(['valid' => false, 'error' => 'Utente non trovato.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = $user['Email'];

$stmt = $mysqli->prepare('SELECT r.ID_ruolo, r.Nome_ruolo, p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
    FROM Utente_Ruolo ur
    JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
    JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
    JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
    WHERE ur.email_utente = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno (prepare).'], JSON_UNESCAPED_UNICODE);
    exit;
}

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

http_response_code(200);
echo json_encode([
    'valid' => true,
    'payload' => $payload,
    'email' => $email,
    'roles' => $roles,
    'permissions' => $permissions,
], JSON_UNESCAPED_UNICODE);
exit;
