<?php
declare(strict_types=1);

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/jwt.php';

header('Content-Type: application/json; charset=utf-8');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if ($authHeader === '' && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}

if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token mancante o non valido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = verify_jwt($matches[1], JWT_SECRET);
if (!$payload || empty($payload['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Token non valido o scaduto.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$payload['user_id'];

$stmt = $mysqli->prepare('
    SELECT Email
    FROM Utenti
    WHERE ID_utente = ?
    LIMIT 1
');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno (prepare).'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Utente non trovato.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = $user['Email'];

$stmt = $mysqli->prepare('
    SELECT r.ID_ruolo, r.Nome_ruolo,
           p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
    FROM Utente_Ruolo ur
    JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
    JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
    JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
    WHERE ur.email_utente = ?
');
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
        $roles[] = [
            'id' => $roleId,
            'name' => $row['Nome_ruolo'],
        ];
    }

    $permId = (int)$row['ID_privilegio'];
    if (!isset($permMap[$permId])) {
        $permMap[$permId] = true;
        $permissions[] = [
            'id' => $permId,
            'name' => $row['Nome_privilegio'],
            'resource' => $row['Risorsa'],
            'action' => $row['Azione'],
        ];
    }
}

$stmt->close();

http_response_code(200);
echo json_encode([
    'user_id' => $userId,
    'email' => $email,
    'roles' => $roles,
    'permissions' => $permissions,
], JSON_UNESCAPED_UNICODE);
exit;