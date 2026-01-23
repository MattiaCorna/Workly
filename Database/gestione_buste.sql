
-- ======================================
-- FILE: gestione_buste_con_ruoli.sql
-- DATABASE: Gestione Buste Paga
-- ======================================

-- Creazione database
CREATE DATABASE IF NOT EXISTS gestione_buste;
USE gestione_buste;

-- ======================================
-- TABELLA: PROFILO CONTRATTO
-- ======================================
CREATE TABLE profilo_contratto (
    id_profilo INT AUTO_INCREMENT PRIMARY KEY,
    livello_dipendente VARCHAR(50) NOT NULL,
    mese_lavorativo VARCHAR(20) NOT NULL,
    maggiorazioni DECIMAL(10,2) DEFAULT 0.00
);

-- ======================================
-- TABELLA: UTENTI
-- ======================================
CREATE TABLE utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    id_profilo INT NOT NULL,

    CONSTRAINT fk_utente_profilo
        FOREIGN KEY (id_profilo)
        REFERENCES profilo_contratto(id_profilo)
        ON DELETE CASCADE
);

-- ======================================
-- TABELLA: BUSTA PAGA
-- ======================================
CREATE TABLE busta_paga (
    id_busta INT AUTO_INCREMENT PRIMARY KEY,
    stipendio_lordo DECIMAL(10,2) NOT NULL,
    stipendio_netto DECIMAL(10,2) NOT NULL,
    tasse_totali DECIMAL(10,2) NOT NULL,
    id_utente INT NOT NULL,

    CONSTRAINT fk_busta_utente
        FOREIGN KEY (id_utente)
        REFERENCES utenti(id_utente)
        ON DELETE CASCADE
);

-- ======================================
-- TABELLA: UTENTE ABBONATO
-- ======================================
CREATE TABLE utente_abbonato (
    id_utente INT PRIMARY KEY,
    data_abbonamento DATE NOT NULL,

    CONSTRAINT fk_abbonato_utente
        FOREIGN KEY (id_utente)
        REFERENCES utenti(id_utente)
        ON DELETE CASCADE
);

-- ======================================
-- TABELLA: UTENTE NON ABBONATO
-- ======================================
CREATE TABLE utente_non_abbonato (
    id_utente INT PRIMARY KEY,

    CONSTRAINT fk_non_abbonato_utente
        FOREIGN KEY (id_utente)
        REFERENCES utenti(id_utente)
        ON DELETE CASCADE
);

-- ======================================
-- CREAZIONE RUOLI
-- ======================================
CREATE ROLE IF NOT EXISTS 'admin';
CREATE ROLE IF NOT EXISTS 'hr';
CREATE ROLE IF NOT EXISTS 'utente';

-- ======================================
-- PERMESSI AI RUOLI
-- ======================================

-- Admin: tutti i permessi
GRANT ALL PRIVILEGES ON gestione_buste.* TO 'admin';

-- HR: gestione buste e profili
GRANT SELECT, INSERT, UPDATE
ON gestione_buste.busta_paga TO 'hr';

GRANT SELECT, INSERT, UPDATE
ON gestione_buste.profilo_contratto TO 'hr';

GRANT SELECT
ON gestione_buste.utenti TO 'hr';

-- Utente: sola lettura buste paga
GRANT SELECT
ON gestione_buste.busta_paga TO 'utente';

-- ======================================
-- ESEMPI DI UTENTI (MODIFICABILI)
-- ======================================
CREATE USER IF NOT EXISTS 'admin_db'@'localhost' IDENTIFIED BY 'admin123';
CREATE USER IF NOT EXISTS 'hr_db'@'localhost' IDENTIFIED BY 'hr123';
CREATE USER IF NOT EXISTS 'utente_db'@'localhost' IDENTIFIED BY 'utente123';

-- Assegnazione ruoli
GRANT 'admin' TO 'admin_db'@'localhost';
GRANT 'hr' TO 'hr_db'@'localhost';
GRANT 'utente' TO 'utente_db'@'localhost';

-- Attivazione ruoli di default
SET DEFAULT ROLE ALL TO 'admin_db'@'localhost';
SET DEFAULT ROLE ALL TO 'hr_db'@'localhost';
SET DEFAULT ROLE ALL TO 'utente_db'@'localhost';

-- ======================================
-- FINE FILE
-- ======================================

