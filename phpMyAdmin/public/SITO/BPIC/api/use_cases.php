<?php
declare(strict_types=1);

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/jwt.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function send_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function request_data(string $method): array
{
    if ($method === 'GET') {
        return $_GET;
    }
    if ($method === 'POST') {
        $body = get_json_body();
        if (!empty($body)) {
            return $body;
        }
        return $_POST;
    }

    return get_json_body();
}

function bearer_token(): ?string
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authHeader === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
        return null;
    }

    return $matches[1];
}

function fetch_user_and_permissions(mysqli $mysqli, int $userId): array
{
    $stmt = $mysqli->prepare('SELECT ID_utente, Email, N_Telefono FROM Utenti WHERE ID_utente = ? LIMIT 1');
    if (!$stmt) {
        send_json(500, ['error' => 'Errore interno (prepare utente).']);
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        send_json(404, ['error' => 'Utente non trovato.']);
    }

    $email = (string)$user['Email'];

    $stmt = $mysqli->prepare('SELECT r.ID_ruolo, r.Nome_ruolo, p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
        FROM Utente_Ruolo ur
        JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
        JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
        JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
        WHERE ur.email_utente = ?');
    if (!$stmt) {
        send_json(500, ['error' => 'Errore interno (prepare permessi).']);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $roles = [];
    $permissions = [];
    $roleMap = [];
    $permMap = [];
    $roleNames = [];

    while ($row = $result->fetch_assoc()) {
        $roleId = (int)$row['ID_ruolo'];
        if (!isset($roleMap[$roleId])) {
            $roleMap[$roleId] = true;
            $roles[] = ['id' => $roleId, 'name' => $row['Nome_ruolo']];
            $roleNames[] = $row['Nome_ruolo'];
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

    return [
        'user' => $user,
        'roles' => $roles,
        'permissions' => $permissions,
        'role_names' => $roleNames,
    ];
}

function has_permission(array $auth, string $resource, string $action): bool
{
    if (in_array('admin', $auth['role_names'], true)) {
        return true;
    }

    foreach ($auth['permissions'] as $perm) {
        $resourceOk = isset($perm['resource']) && $perm['resource'] === $resource;
        $actionOk = isset($perm['action']) && ($perm['action'] === $action || $perm['action'] === 'ALL');
        if ($resourceOk && $actionOk) {
            return true;
        }
    }

    return false;
}

function require_permission(array $auth, string $resource, string $action): void
{
    if (!has_permission($auth, $resource, $action)) {
        send_json(403, [
            'error' => 'Permessi insufficienti.',
            'required' => ['resource' => $resource, 'action' => $action],
        ]);
    }
}

function fetch_view(mysqli $mysqli, string $viewName, bool $isAdmin, int $userId): array
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $viewName)) {
        send_json(400, ['error' => 'Nome vista non valido.']);
    }

    $sql = "SELECT * FROM {$viewName}";
    $types = '';
    $params = [];

    if (!$isAdmin && in_array($viewName, [
        'v_generazione_busta_paga',
        'v_download_pdf',
        'v_invio_pdf_email',
        'v_archivio_buste_paga',
        'v_confronto_buste_paga',
    ], true)) {
        $sql .= ' WHERE ID_utente = ?';
        $types = 'i';
        $params[] = $userId;
    }

    $sql .= ' ORDER BY 1 DESC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        send_json(500, ['error' => 'Errore interno (prepare vista).']);
    }

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    send_json(405, ['error' => 'Metodo non consentito.']);
}

$token = bearer_token();
if ($token === null) {
    send_json(401, ['error' => 'Token Bearer mancante.']);
}

$payload = verify_jwt($token, JWT_SECRET);
if (!$payload || empty($payload['user_id'])) {
    send_json(401, ['error' => 'JWT non valido o scaduto.']);
}

$auth = fetch_user_and_permissions($mysqli, (int)$payload['user_id']);
$isAdmin = in_array('admin', $auth['role_names'], true);
$currentUserId = (int)$auth['user']['ID_utente'];

$input = request_data($method);
$useCase = (string)($input['use_case'] ?? $_GET['use_case'] ?? '');

if ($useCase === '') {
    send_json(400, [
        'error' => 'Parametro use_case mancante.',
        'example' => [
            'GET /SITO/BPIC/api/use_cases.php?use_case=generazione_busta_paga_list',
            'POST /SITO/BPIC/api/use_cases.php {"use_case":"generazione_busta_paga_create","stipendio_lordo":1800,"stipendio_netto":1300,"tasse_totali":500}',
        ],
    ]);
}

switch ($useCase) {
    case 'generazione_busta_paga_list':
        require_permission($auth, 'buste_paga', 'INSERT');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_generazione_busta_paga', $isAdmin, $currentUserId),
        ]);

    case 'generazione_busta_paga_create':
        require_permission($auth, 'buste_paga', 'INSERT');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $lordo = (float)($input['stipendio_lordo'] ?? 0);
        $netto = (float)($input['stipendio_netto'] ?? 0);
        $tasse = (float)($input['tasse_totali'] ?? 0);

        if ($lordo <= 0 || $netto <= 0 || $tasse < 0) {
            send_json(422, ['error' => 'Valori busta paga non validi.']);
        }

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('INSERT INTO Busta_paga (Stipendio_lordo, Stipendio_netto, Tasse_totali) VALUES (?, ?, ?)');
            if (!$stmt) {
                throw new RuntimeException('Prepare insert busta fallita.');
            }
            $stmt->bind_param('ddd', $lordo, $netto, $tasse);
            $stmt->execute();
            $idBusta = (int)$stmt->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare('UPDATE Utenti SET ID_busta = ? WHERE ID_utente = ?');
            if (!$stmt) {
                throw new RuntimeException('Prepare update utente fallita.');
            }
            $stmt->bind_param('ii', $idBusta, $currentUserId);
            $stmt->execute();
            $stmt->close();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore durante la creazione della busta paga.']);
        }

        send_json(201, [
            'use_case' => $useCase,
            'message' => 'Busta paga creata con successo.',
            'id_busta' => $idBusta,
        ]);

    case 'download_pdf':
        require_permission($auth, 'pdf', 'SELECT');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_download_pdf', $isAdmin, $currentUserId),
            'note' => 'Questa API espone i dati necessari al download PDF. La generazione file e demandata al frontend o a un servizio PDF.',
        ]);

    case 'invio_pdf_email':
        require_permission($auth, 'email', 'INSERT');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $idBusta = (int)($input['id_busta'] ?? 0);
        $destinatario = trim((string)($input['destinatario'] ?? $auth['user']['Email'] ?? ''));
        if ($idBusta <= 0 || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            send_json(422, ['error' => 'Parametri invio non validi.']);
        }

        $sql = 'SELECT * FROM v_invio_pdf_email WHERE ID_busta = ?';
        if (!$isAdmin) {
            $sql .= ' AND ID_utente = ?';
        }

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (prepare invio).']);
        }
        if ($isAdmin) {
            $stmt->bind_param('i', $idBusta);
        } else {
            $stmt->bind_param('ii', $idBusta, $currentUserId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            send_json(404, ['error' => 'Busta paga non trovata per invio email.']);
        }

        send_json(200, [
            'use_case' => $useCase,
            'message' => 'Invio email simulato con successo.',
            'to' => $destinatario,
            'payload' => $row,
        ]);

    case 'archivio_buste_paga_list':
        require_permission($auth, 'archivio', 'SELECT');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_archivio_buste_paga', $isAdmin, $currentUserId),
        ]);

    case 'archivio_buste_paga_delete':
        require_permission($auth, 'archivio', 'SELECT');
        if ($method !== 'DELETE') {
            send_json(405, ['error' => 'Metodo richiesto: DELETE']);
        }

        $idBusta = (int)($input['id_busta'] ?? 0);
        if ($idBusta <= 0) {
            send_json(422, ['error' => 'id_busta non valido.']);
        }

        $sql = 'DELETE FROM Confronta WHERE ID_busta = ?';
        if (!$isAdmin) {
            $sql .= ' AND ID_utente = ?';
        }

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (prepare delete archivio).']);
        }
        if ($isAdmin) {
            $stmt->bind_param('i', $idBusta);
        } else {
            $stmt->bind_param('ii', $idBusta, $currentUserId);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        send_json(200, [
            'use_case' => $useCase,
            'deleted_rows' => $affected,
        ]);

    case 'confronto_buste_paga_list':
        require_permission($auth, 'confronto', 'SELECT');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_confronto_buste_paga', $isAdmin, $currentUserId),
        ]);

    case 'confronto_buste_paga_create':
        require_permission($auth, 'confronto', 'SELECT');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $idA = (int)($input['id_busta_a'] ?? 0);
        $idB = (int)($input['id_busta_b'] ?? 0);
        if ($idA <= 0 || $idB <= 0 || $idA === $idB) {
            send_json(422, ['error' => 'id_busta_a/id_busta_b non validi.']);
        }

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('INSERT IGNORE INTO Confronta (ID_utente, ID_busta, Data_confronto) VALUES (?, ?, NOW())');
            if (!$stmt) {
                throw new RuntimeException('Prepare confronto fallita.');
            }
            $stmt->bind_param('ii', $currentUserId, $idA);
            $stmt->execute();
            $stmt->bind_param('ii', $currentUserId, $idB);
            $stmt->execute();
            $stmt->close();
            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore durante il salvataggio del confronto.']);
        }

        send_json(201, [
            'use_case' => $useCase,
            'message' => 'Buste aggiunte al confronto.',
            'rows' => fetch_view($mysqli, 'v_confronto_buste_paga', $isAdmin, $currentUserId),
        ]);

    case 'gestione_utenti_list':
        require_permission($auth, 'utenti', 'ALL');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_gestione_utenti', true, $currentUserId),
        ]);

    case 'gestione_utenti_create':
        require_permission($auth, 'utenti', 'ALL');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $email = trim((string)($input['email'] ?? ''));
        $telefono = trim((string)($input['n_telefono'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $idRuolo = (int)($input['id_ruolo'] ?? 3);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            send_json(422, ['error' => 'Dati utente non validi (email/password).']);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('INSERT INTO Utenti (N_Telefono, Email, ID_busta, Password_hash) VALUES (?, ?, NULL, ?)');
            if (!$stmt) {
                throw new RuntimeException('Prepare insert utente fallita.');
            }
            $stmt->bind_param('sss', $telefono, $email, $passwordHash);
            $stmt->execute();
            $userId = (int)$stmt->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare('INSERT INTO Utente_Ruolo (ID_ruolo, email_utente) VALUES (?, ?)');
            if (!$stmt) {
                throw new RuntimeException('Prepare ruolo utente fallita.');
            }
            $stmt->bind_param('is', $idRuolo, $email);
            $stmt->execute();
            $stmt->close();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore creazione utente (forse email gia presente o ruolo non valido).']);
        }

        send_json(201, [
            'use_case' => $useCase,
            'message' => 'Utente creato con successo.',
            'user_id' => $userId,
            'email' => $email,
            'id_ruolo' => $idRuolo,
        ]);

    case 'gestione_utenti_update':
        require_permission($auth, 'utenti', 'ALL');
        if ($method !== 'PUT') {
            send_json(405, ['error' => 'Metodo richiesto: PUT']);
        }

        $userId = (int)($input['user_id'] ?? 0);
        if ($userId <= 0) {
            send_json(422, ['error' => 'user_id non valido.']);
        }

        $telefono = array_key_exists('n_telefono', $input) ? trim((string)$input['n_telefono']) : null;
        $password = array_key_exists('password', $input) ? (string)$input['password'] : null;
        $idRuolo = array_key_exists('id_ruolo', $input) ? (int)$input['id_ruolo'] : null;

        $stmt = $mysqli->prepare('SELECT Email FROM Utenti WHERE ID_utente = ? LIMIT 1');
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (select utente).']);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $existing = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if (!$existing) {
            send_json(404, ['error' => 'Utente non trovato.']);
        }

        $mysqli->begin_transaction();
        try {
            if ($telefono !== null) {
                $stmt = $mysqli->prepare('UPDATE Utenti SET N_Telefono = ? WHERE ID_utente = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update telefono fallita.');
                }
                $stmt->bind_param('si', $telefono, $userId);
                $stmt->execute();
                $stmt->close();
            }

            if ($password !== null) {
                if (strlen($password) < 6) {
                    throw new RuntimeException('Password troppo corta.');
                }
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare('UPDATE Utenti SET Password_hash = ? WHERE ID_utente = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update password fallita.');
                }
                $stmt->bind_param('si', $passwordHash, $userId);
                $stmt->execute();
                $stmt->close();
            }

            if ($idRuolo !== null) {
                $email = (string)$existing['Email'];
                $stmt = $mysqli->prepare('DELETE FROM Utente_Ruolo WHERE email_utente = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare delete ruolo utente fallita.');
                }
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->close();

                $stmt = $mysqli->prepare('INSERT INTO Utente_Ruolo (ID_ruolo, email_utente) VALUES (?, ?)');
                if (!$stmt) {
                    throw new RuntimeException('Prepare insert ruolo utente fallita.');
                }
                $stmt->bind_param('is', $idRuolo, $email);
                $stmt->execute();
                $stmt->close();
            }

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore aggiornamento utente.', 'detail' => $e->getMessage()]);
        }

        send_json(200, [
            'use_case' => $useCase,
            'message' => 'Utente aggiornato con successo.',
            'user_id' => $userId,
        ]);

    case 'gestione_utenti_delete':
        require_permission($auth, 'utenti', 'ALL');
        if ($method !== 'DELETE') {
            send_json(405, ['error' => 'Metodo richiesto: DELETE']);
        }

        $userId = (int)($input['user_id'] ?? 0);
        if ($userId <= 0) {
            send_json(422, ['error' => 'user_id non valido.']);
        }

        if ($userId === $currentUserId) {
            send_json(409, ['error' => 'Non puoi eliminare il tuo stesso account da questa API.']);
        }

        $stmt = $mysqli->prepare('DELETE FROM Utenti WHERE ID_utente = ?');
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (delete utente).']);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        send_json(200, [
            'use_case' => $useCase,
            'deleted_rows' => $deleted,
        ]);

    case 'gestione_ruoli_list':
        require_permission($auth, 'ruoli', 'ALL');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_gestione_ruoli', true, $currentUserId),
        ]);

    case 'gestione_ruoli_create':
        require_permission($auth, 'ruoli', 'ALL');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $nomeRuolo = trim((string)($input['nome_ruolo'] ?? ''));
        $descrizione = trim((string)($input['descrizione'] ?? ''));
        $attivo = isset($input['attivo']) ? (int)(bool)$input['attivo'] : 1;
        $privilegi = $input['privilegi'] ?? [];

        if ($nomeRuolo === '') {
            send_json(422, ['error' => 'nome_ruolo obbligatorio.']);
        }
        if (!is_array($privilegi)) {
            send_json(422, ['error' => 'privilegi deve essere un array di ID privilegio.']);
        }

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('INSERT INTO Ruoli (Nome_ruolo, Descrizione, Attivo) VALUES (?, ?, ?)');
            if (!$stmt) {
                throw new RuntimeException('Prepare insert ruolo fallita.');
            }
            $stmt->bind_param('ssi', $nomeRuolo, $descrizione, $attivo);
            $stmt->execute();
            $idRuolo = (int)$stmt->insert_id;
            $stmt->close();

            if (!empty($privilegi)) {
                $stmt = $mysqli->prepare('INSERT INTO Ruolo_Privilegio (ID_ruolo, ID_privilegio, Data_assegnazione) VALUES (?, ?, NOW())');
                if (!$stmt) {
                    throw new RuntimeException('Prepare ruolo_privilegio fallita.');
                }
                foreach ($privilegi as $idPrivilegioRaw) {
                    $idPrivilegio = (int)$idPrivilegioRaw;
                    if ($idPrivilegio <= 0) {
                        continue;
                    }
                    $stmt->bind_param('ii', $idRuolo, $idPrivilegio);
                    $stmt->execute();
                }
                $stmt->close();
            }

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore creazione ruolo.', 'detail' => $e->getMessage()]);
        }

        send_json(201, [
            'use_case' => $useCase,
            'id_ruolo' => $idRuolo,
            'message' => 'Ruolo creato con successo.',
        ]);

    case 'gestione_ruoli_update':
        require_permission($auth, 'ruoli', 'ALL');
        if ($method !== 'PUT') {
            send_json(405, ['error' => 'Metodo richiesto: PUT']);
        }

        $idRuolo = (int)($input['id_ruolo'] ?? 0);
        if ($idRuolo <= 0) {
            send_json(422, ['error' => 'id_ruolo non valido.']);
        }

        $nomeRuolo = array_key_exists('nome_ruolo', $input) ? trim((string)$input['nome_ruolo']) : null;
        $descrizione = array_key_exists('descrizione', $input) ? trim((string)$input['descrizione']) : null;
        $attivo = array_key_exists('attivo', $input) ? (int)(bool)$input['attivo'] : null;
        $privilegi = array_key_exists('privilegi', $input) ? $input['privilegi'] : null;

        $mysqli->begin_transaction();
        try {
            if ($nomeRuolo !== null) {
                $stmt = $mysqli->prepare('UPDATE Ruoli SET Nome_ruolo = ? WHERE ID_ruolo = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update nome ruolo fallita.');
                }
                $stmt->bind_param('si', $nomeRuolo, $idRuolo);
                $stmt->execute();
                $stmt->close();
            }
            if ($descrizione !== null) {
                $stmt = $mysqli->prepare('UPDATE Ruoli SET Descrizione = ? WHERE ID_ruolo = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update descrizione ruolo fallita.');
                }
                $stmt->bind_param('si', $descrizione, $idRuolo);
                $stmt->execute();
                $stmt->close();
            }
            if ($attivo !== null) {
                $stmt = $mysqli->prepare('UPDATE Ruoli SET Attivo = ? WHERE ID_ruolo = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update attivo ruolo fallita.');
                }
                $stmt->bind_param('ii', $attivo, $idRuolo);
                $stmt->execute();
                $stmt->close();
            }

            if ($privilegi !== null) {
                if (!is_array($privilegi)) {
                    throw new RuntimeException('privilegi deve essere un array.');
                }

                $stmt = $mysqli->prepare('DELETE FROM Ruolo_Privilegio WHERE ID_ruolo = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare delete ruolo_privilegio fallita.');
                }
                $stmt->bind_param('i', $idRuolo);
                $stmt->execute();
                $stmt->close();

                if (!empty($privilegi)) {
                    $stmt = $mysqli->prepare('INSERT INTO Ruolo_Privilegio (ID_ruolo, ID_privilegio, Data_assegnazione) VALUES (?, ?, NOW())');
                    if (!$stmt) {
                        throw new RuntimeException('Prepare insert ruolo_privilegio fallita.');
                    }
                    foreach ($privilegi as $idPrivRaw) {
                        $idPriv = (int)$idPrivRaw;
                        if ($idPriv <= 0) {
                            continue;
                        }
                        $stmt->bind_param('ii', $idRuolo, $idPriv);
                        $stmt->execute();
                    }
                    $stmt->close();
                }
            }

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore aggiornamento ruolo.', 'detail' => $e->getMessage()]);
        }

        send_json(200, [
            'use_case' => $useCase,
            'message' => 'Ruolo aggiornato con successo.',
            'id_ruolo' => $idRuolo,
        ]);

    case 'gestione_ruoli_delete':
        require_permission($auth, 'ruoli', 'ALL');
        if ($method !== 'DELETE') {
            send_json(405, ['error' => 'Metodo richiesto: DELETE']);
        }

        $idRuolo = (int)($input['id_ruolo'] ?? 0);
        if ($idRuolo <= 0) {
            send_json(422, ['error' => 'id_ruolo non valido.']);
        }

        $stmt = $mysqli->prepare('DELETE FROM Ruoli WHERE ID_ruolo = ?');
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (delete ruolo).']);
        }
        $stmt->bind_param('i', $idRuolo);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        send_json(200, [
            'use_case' => $useCase,
            'deleted_rows' => $deleted,
        ]);

    case 'gestione_privilegi_list':
        require_permission($auth, 'privilegi', 'ALL');
        send_json(200, [
            'use_case' => $useCase,
            'rows' => fetch_view($mysqli, 'v_gestione_privilegi', true, $currentUserId),
        ]);

    case 'gestione_privilegi_create':
        require_permission($auth, 'privilegi', 'ALL');
        if ($method !== 'POST') {
            send_json(405, ['error' => 'Metodo richiesto: POST']);
        }

        $nome = trim((string)($input['nome_privilegio'] ?? ''));
        $descrizione = trim((string)($input['descrizione'] ?? ''));
        $risorsa = trim((string)($input['risorsa'] ?? ''));
        $azione = strtoupper(trim((string)($input['azione'] ?? '')));

        if ($nome === '' || $risorsa === '' || !in_array($azione, ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'ALL'], true)) {
            send_json(422, ['error' => 'Dati privilegio non validi.']);
        }

        $stmt = $mysqli->prepare('INSERT INTO Privilegi (Nome_privilegio, Descrizione, Risorsa, Azione) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (insert privilegio).']);
        }
        $stmt->bind_param('ssss', $nome, $descrizione, $risorsa, $azione);
        $stmt->execute();
        $idPrivilegio = (int)$stmt->insert_id;
        $stmt->close();

        send_json(201, [
            'use_case' => $useCase,
            'id_privilegio' => $idPrivilegio,
            'message' => 'Privilegio creato con successo.',
        ]);

    case 'gestione_privilegi_update':
        require_permission($auth, 'privilegi', 'ALL');
        if ($method !== 'PUT') {
            send_json(405, ['error' => 'Metodo richiesto: PUT']);
        }

        $idPrivilegio = (int)($input['id_privilegio'] ?? 0);
        if ($idPrivilegio <= 0) {
            send_json(422, ['error' => 'id_privilegio non valido.']);
        }

        $nome = array_key_exists('nome_privilegio', $input) ? trim((string)$input['nome_privilegio']) : null;
        $descrizione = array_key_exists('descrizione', $input) ? trim((string)$input['descrizione']) : null;
        $risorsa = array_key_exists('risorsa', $input) ? trim((string)$input['risorsa']) : null;
        $azione = array_key_exists('azione', $input) ? strtoupper(trim((string)$input['azione'])) : null;

        if ($azione !== null && !in_array($azione, ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'ALL'], true)) {
            send_json(422, ['error' => 'azione non valida.']);
        }

        $mysqli->begin_transaction();
        try {
            if ($nome !== null) {
                $stmt = $mysqli->prepare('UPDATE Privilegi SET Nome_privilegio = ? WHERE ID_privilegio = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update nome privilegio fallita.');
                }
                $stmt->bind_param('si', $nome, $idPrivilegio);
                $stmt->execute();
                $stmt->close();
            }
            if ($descrizione !== null) {
                $stmt = $mysqli->prepare('UPDATE Privilegi SET Descrizione = ? WHERE ID_privilegio = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update descrizione privilegio fallita.');
                }
                $stmt->bind_param('si', $descrizione, $idPrivilegio);
                $stmt->execute();
                $stmt->close();
            }
            if ($risorsa !== null) {
                $stmt = $mysqli->prepare('UPDATE Privilegi SET Risorsa = ? WHERE ID_privilegio = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update risorsa privilegio fallita.');
                }
                $stmt->bind_param('si', $risorsa, $idPrivilegio);
                $stmt->execute();
                $stmt->close();
            }
            if ($azione !== null) {
                $stmt = $mysqli->prepare('UPDATE Privilegi SET Azione = ? WHERE ID_privilegio = ?');
                if (!$stmt) {
                    throw new RuntimeException('Prepare update azione privilegio fallita.');
                }
                $stmt->bind_param('si', $azione, $idPrivilegio);
                $stmt->execute();
                $stmt->close();
            }

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            send_json(500, ['error' => 'Errore aggiornamento privilegio.', 'detail' => $e->getMessage()]);
        }

        send_json(200, [
            'use_case' => $useCase,
            'id_privilegio' => $idPrivilegio,
            'message' => 'Privilegio aggiornato con successo.',
        ]);

    case 'gestione_privilegi_delete':
        require_permission($auth, 'privilegi', 'ALL');
        if ($method !== 'DELETE') {
            send_json(405, ['error' => 'Metodo richiesto: DELETE']);
        }

        $idPrivilegio = (int)($input['id_privilegio'] ?? 0);
        if ($idPrivilegio <= 0) {
            send_json(422, ['error' => 'id_privilegio non valido.']);
        }

        $stmt = $mysqli->prepare('DELETE FROM Privilegi WHERE ID_privilegio = ?');
        if (!$stmt) {
            send_json(500, ['error' => 'Errore interno (delete privilegio).']);
        }
        $stmt->bind_param('i', $idPrivilegio);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        send_json(200, [
            'use_case' => $useCase,
            'deleted_rows' => $deleted,
        ]);

    default:
        send_json(404, ['error' => 'Use case non riconosciuto.']);
}
