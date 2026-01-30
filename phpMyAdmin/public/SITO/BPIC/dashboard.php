<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f6f8;
        }
        .home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #667eea;
            color: white;
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
            transition: transform 0.3s, background 0.3s;
        }
        .home-btn:hover {
            background: #764ba2;
            transform: scale(1.1);
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        p {
            text-align: center;
            font-size: 18px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<a href="../index.php" class="home-btn" title="Home">🏠</a>

<div class="container">
    <h1>Benvenuto nella Dashboard</h1>
    <p>Sei loggato come <?php echo htmlspecialchars($_SESSION['email']); ?></p>

    <?php
    // Recupera ruoli e privilegi dalla sessione se presenti, altrimenti dal DB
    $roles = $_SESSION['roles'] ?? null;
    $permissions = $_SESSION['permissions'] ?? null;

    if (!$roles || !$permissions) {
        require_once __DIR__ . "/database.php";
        $email = $_SESSION['email'];
        $stmt = $mysqli->prepare('SELECT r.ID_ruolo, r.Nome_ruolo, p.ID_privilegio, p.Nome_privilegio, p.Risorsa, p.Azione
            FROM Utente_Ruolo ur
            JOIN Ruoli r ON r.ID_ruolo = ur.ID_ruolo
            JOIN Ruolo_Privilegio rp ON rp.ID_ruolo = r.ID_ruolo
            JOIN Privilegi p ON p.ID_privilegio = rp.ID_privilegio
            WHERE ur.email_utente = ?');
        if ($stmt) {
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
            // Salva in sessione per future richieste
            $_SESSION['roles'] = $roles;
            $_SESSION['permissions'] = $permissions;
        }
    }
    ?>

    <div style="margin-top:20px; text-align:center;">
        <?php
        $roleNames = array_map(fn($r) => $r['name'], $roles ?? []);
        $isAdmin = in_array('admin', $roleNames, true);
        $isAbbonato = in_array('utente_abbonato', $roleNames, true);
        $isNonAbbonato = in_array('utente_non_abbonato', $roleNames, true);
        ?>

        <p>
            <strong>Stato:</strong>
            <?php if ($isAdmin): ?> <span style="color:green; font-weight:bold;">ADMIN</span>
            <?php elseif ($isAbbonato): ?> <span style="color:blue; font-weight:bold;">Utente abbonato</span>
            <?php elseif ($isNonAbbonato): ?> <span style="color:orange; font-weight:bold;">Utente non abbonato</span>
            <?php else: ?> <span style="color:grey;">Non definito</span>
            <?php endif; ?>
        </p>

        <strong>Ruoli:</strong>
        <?php if (!empty($roles)): ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($roles as $r): ?>
                    <li><?php echo htmlspecialchars($r['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nessun ruolo assegnato</p>
        <?php endif; ?>

        <strong>Privilegi:</strong>
        <?php if (!empty($permissions)): ?>
            <ul style="text-align:left; display:inline-block;">
                <?php foreach ($permissions as $p): ?>
                    <li><?php echo htmlspecialchars($p['name']); ?> — <em><?php echo htmlspecialchars($p['resource']); ?> (<?php echo htmlspecialchars($p['action']); ?>)</em></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nessun privilegio trovato</p>
        <?php endif; ?>
    </div>

    <hr>

    <div style="margin-top:20px; text-align:center;">
        <h2>Token personale</h2>
        <p>Puoi generare un token valido per 24 ore per testare le API o condividerlo con servizi esterni.</p>

        <div style="max-width:700px;margin:0 auto;text-align:left;">
            <textarea id="tokenArea" rows="3" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;" readonly></textarea>
            <div style="margin-top:8px;display:flex;gap:8px;">
                <button id="genBtn">Genera token</button>
                <button id="copyBtn">Copia token</button>
                <button id="verifyBtn">Verifica token</button>
            </div>
            <div id="tokenResult" style="margin-top:12px;color:#333;"></div>
        </div>
    </div>

    <a href="logout.php">Logout</a>

    <script>
        document.getElementById('genBtn').addEventListener('click', async function () {
            const r = await fetch('api/generate_token.php', { method: 'POST' });
            if (!r.ok) {
                document.getElementById('tokenResult').textContent = 'Errore nella generazione del token.';
                return;
            }
            const data = await r.json();
            if (data.token) {
                document.getElementById('tokenArea').value = data.token;
                document.getElementById('tokenResult').textContent = 'Token generato (scade in ' + data.expires_in + ' secondi).';
            } else if (data.error) {
                document.getElementById('tokenResult').textContent = data.error;
            }
        });

        document.getElementById('copyBtn').addEventListener('click', async function () {
            const token = document.getElementById('tokenArea').value;
            if (!token) {
                document.getElementById('tokenResult').textContent = 'Nessun token da copiare.';
                return;
            }
            try {
                await navigator.clipboard.writeText(token);
                document.getElementById('tokenResult').textContent = 'Token copiato negli appunti.';
            } catch (e) {
                document.getElementById('tokenResult').textContent = 'Impossibile copiare (browser non supportato).';
            }
        });

        document.getElementById('verifyBtn').addEventListener('click', async function () {
            const token = document.getElementById('tokenArea').value;
            if (!token) {
                document.getElementById('tokenResult').textContent = 'Nessun token da verificare.';
                return;
            }
            const r = await fetch('api/verify_token.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token })
            });
            const data = await r.json();
            if (!r.ok) {
                document.getElementById('tokenResult').textContent = data.error || 'Errore nella verifica.';
                return;
            }
            if (!data.valid) {
                document.getElementById('tokenResult').textContent = 'Token non valido: ' + (data.error ?? '');
                return;
            }
            // Mostra ruolo e privilegi di risposta
            let out = 'Token valido per: ' + (data.email ?? 'utente') + '\n';
            out += 'Ruoli: ' + ((data.roles || []).map(r => r.name).join(', ') || 'nessuno') + '\n';
            out += 'Privilegi: ' + ((data.permissions || []).map(p => p.name).join(', ') || 'nessuno');
            document.getElementById('tokenResult').textContent = out;
        });
    </script>
</div>

</body>
</html>