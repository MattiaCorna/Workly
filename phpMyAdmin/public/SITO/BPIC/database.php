<?php
// Parametri di configurazione
$host = '127.0.0.1';
$db = 'gestione_utenti_bp';
$user = 'utente_phpmyadmin';
$pass = 'password_sicura';
$charset = 'utf8mb4';

// Creazione della connessione MySQLi
$mysqli = new mysqli($host, $user, $pass, $db);

// Verifica errore di connessione
if ($mysqli->connect_error) {
    die("Errore di connessione: " . $mysqli->connect_error);
}

// Imposta charset
$mysqli->set_charset($charset);
?>