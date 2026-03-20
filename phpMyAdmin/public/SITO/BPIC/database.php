<?php
// Parametri di configurazione
$host = '127.0.0.1';
$db = 'gestione_utenti_bp';
$user = 'utente_phpmyadmin';
$pass = 'password_sicura';
$charset = 'utf8mb4';

// Verifica che l'estensione PDO MySQL sia disponibile
if (!extension_loaded('pdo_mysql')) {
    http_response_code(500);
    echo "<h1>Configurazione PHP incompleta</h1>";
    echo "<p>L'estensione <strong>pdo_mysql</strong> non è disponibile. Abilita l'estensione PHP PDO per MySQL e riavvia il server.</p>";
    exit;
}

try {
    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Errore di connessione: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

if (!class_exists('PDOMysqliCompat')) {
    class PDOMysqliResultCompat
    {
        private PDOStatement $stmt;

        public function __construct(PDOStatement $stmt)
        {
            $this->stmt = $stmt;
        }

        public function fetch_assoc(): ?array
        {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            return $row === false ? null : $row;
        }

        public function fetch_fields(): array
        {
            $fields = [];
            $count = $this->stmt->columnCount();
            for ($i = 0; $i < $count; $i++) {
                $meta = $this->stmt->getColumnMeta($i);
                $name = $meta['name'] ?? ('col_' . $i);
                $fields[] = (object)['name' => $name];
            }
            return $fields;
        }

        public function free(): void
        {
            $this->stmt->closeCursor();
        }
    }

    class PDOMysqliStmtCompat
    {
        private PDOMysqliCompat $conn;
        private PDOStatement $stmt;
        private array $boundParams = [];
        public int $affected_rows = 0;
        public int $insert_id = 0;

        public function __construct(PDOMysqliCompat $conn, PDOStatement $stmt)
        {
            $this->conn = $conn;
            $this->stmt = $stmt;
        }

        public function bind_param(string $types, ...$vars): bool
        {
            $this->boundParams = [];
            foreach ($vars as $var) {
                $this->boundParams[] = $var;
            }
            return true;
        }

        public function execute(): bool
        {
            try {
                $params = [];
                foreach ($this->boundParams as $value) {
                    $params[] = $value;
                }
                $ok = $this->stmt->execute($params);
                $this->affected_rows = $this->stmt->rowCount();
                $this->insert_id = (int)$this->conn->pdo->lastInsertId();
                $this->conn->error = '';
                return $ok;
            } catch (PDOException $e) {
                $this->conn->error = $e->getMessage();
                return false;
            }
        }

        public function get_result(): PDOMysqliResultCompat
        {
            return new PDOMysqliResultCompat($this->stmt);
        }

        public function close(): void
        {
            $this->stmt->closeCursor();
        }
    }

    class PDOMysqliCompat
    {
        public PDO $pdo;
        public string $error = '';

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        public function prepare(string $sql)
        {
            try {
                $stmt = $this->pdo->prepare($sql);
                $this->error = '';
                return new PDOMysqliStmtCompat($this, $stmt);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function query(string $sql)
        {
            try {
                $stmt = $this->pdo->query($sql);
                $this->error = '';
                return new PDOMysqliResultCompat($stmt);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function begin_transaction(): bool
        {
            return $this->pdo->beginTransaction();
        }

        public function commit(): bool
        {
            return $this->pdo->commit();
        }

        public function rollback(): bool
        {
            if ($this->pdo->inTransaction()) {
                return $this->pdo->rollBack();
            }
            return true;
        }
    }
}

$mysqli = new PDOMysqliCompat($pdo);
?>