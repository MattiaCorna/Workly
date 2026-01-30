<?php
// Parametri di configurazione
$host = '127.0.0.1';
$db = 'gestione_utenti_bp';
$user = 'utente_phpmyadmin';
$pass = 'password_sicura';
$charset = 'utf8mb4';

// Verifica che l'estensione mysqli sia disponibile
if (!class_exists('mysqli')) {
    // Errore chiaro per sviluppo: l'estensione mysqli non è abilitata
    http_response_code(500);
    echo "<h1>Configurazione PHP incompleta</h1>";
    echo "<p>L'estensione <strong>mysqli</strong> non è disponibile. Abilita l'estensione PHP per MySQL (es. installa e abilita php-mysqli / php8.0-mysqli) e riavvia il server.</p>";
    exit;
}

// Creazione della connessione MySQLi
$mysqli = new mysqli($host, $user, $pass, $db);

// Verifica errore di connessione
if ($mysqli->connect_error) {
    die("Errore di connessione: " . $mysqli->connect_error);
}

// Imposta charset
$mysqli->set_charset($charset);
?>