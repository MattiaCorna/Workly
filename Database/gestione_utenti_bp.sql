-- ============================================================
-- phpMyAdmin SQL Dump
-- version 5.2.3 | https://www.phpmyadmin.net/
-- Host: localhost
-- Versione del server: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versione PHP: 8.3.6
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- CREAZIONE E SELEZIONE DEL DATABASE
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gestione_utenti_bp`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `gestione_utenti_bp`;

-- ============================================================
-- STRUTTURA DELLE TABELLE
-- ============================================================

CREATE TABLE `Busta_paga` (
  `ID_busta`        int(11)        NOT NULL AUTO_INCREMENT,
  `Stipendio_lordo` decimal(10,2)  NOT NULL,
  `Stipendio_netto` decimal(10,2)  NOT NULL,
  `Tasse_totali`    decimal(10,2)  NOT NULL,
  PRIMARY KEY (`ID_busta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `Utenti` (
  `ID_utente`     int(11)       NOT NULL AUTO_INCREMENT,
  `N_Telefono`    varchar(20)   DEFAULT NULL,
  `Email`         varchar(100)  DEFAULT NULL,
  `ID_busta`      int(11)       DEFAULT NULL,
  `Password_hash` varchar(255)  NOT NULL,
  PRIMARY KEY (`ID_utente`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_email` (`Email`),
  KEY `ID_busta`  (`ID_busta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Utenti del sistema (gerarchia collassata)'
  AUTO_INCREMENT=5;

-- --------------------------------------------------------

CREATE TABLE `Confronta` (
  `ID_utente`      int(11)    NOT NULL,
  `ID_busta`       int(11)    NOT NULL,
  `Data_confronto` timestamp  NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID_utente`, `ID_busta`),
  KEY `idx_confronta_utente` (`ID_utente`),
  KEY `idx_confronta_busta`  (`ID_busta`),
  KEY `idx_confronta_data`   (`Data_confronto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Confronti tra buste paga (solo utenti abbonati)';

-- --------------------------------------------------------

CREATE TABLE `Impostazioni_contratto` (
  `ID_utente`                 int(100)      NOT NULL,
  `tipologia_dipendente`      enum('Statale','Mettalmeccanico','Commerciale','') NOT NULL DEFAULT '',
  `Livello_dipendente`        varchar(10)   NOT NULL DEFAULT '',
  `Maggiorazione_notturna`    decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Maggiorazione_straordinaria` decimal(6,2) NOT NULL DEFAULT 0.00,
  `Maggiorazione_festiva`     decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Maggiorazione_prefestiva`  decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Indennita_malattia`        decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Indennita_reperibilita`    decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Indennita_trasferta`       decimal(6,2)  NOT NULL DEFAULT 0.00,
  `Tredicesima`               enum('SI','NO') NOT NULL DEFAULT 'NO',
  `Quattordicesima`           enum('SI','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID_utente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `Profilo_contratto` (
  `ID_utente`                 int(100)      NOT NULL AUTO_INCREMENT,
  `tipologia_dipendente`      enum('Statale','Mettalmeccanico','Commerciale','') NOT NULL,
  `Livello_dipendente`        varchar(10)   NOT NULL DEFAULT '',
  `Maggiorazione_notturna`    decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '%',
  `Maggiorazione_straordinaria` decimal(6,2) NOT NULL DEFAULT 0.00 COMMENT '%',
  `Maggiorazione_festiva`     decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '%',
  `Maggiorazione_prefestiva`  decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '%',
  `Indennita_malattia`        decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '%',
  `Indennita_reperibilita`    decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '€/ora',
  `Indennita_trasferta`       decimal(6,2)  NOT NULL DEFAULT 0.00 COMMENT '€/ora',
  `Tredicesima`               enum('SI','NO') NOT NULL DEFAULT 'NO',
  `Quattordicesima`           enum('SI','NO') NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`ID_utente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `Privilegi` (
  `ID_privilegio`  int(11)       NOT NULL AUTO_INCREMENT,
  `Nome_privilegio` varchar(100) NOT NULL,
  `Descrizione`    text          DEFAULT NULL,
  `Risorsa`        varchar(100)  NOT NULL,
  `Azione`         enum('SELECT','INSERT','UPDATE','DELETE','ALL') NOT NULL,
  PRIMARY KEY (`ID_privilegio`),
  UNIQUE KEY `Nome_privilegio` (`Nome_privilegio`),
  KEY `idx_risorsa_azione` (`Risorsa`, `Azione`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Privilegi atomici del sistema'
  AUTO_INCREMENT=11;

-- --------------------------------------------------------

CREATE TABLE `Ruoli` (
  `ID_ruolo`    int(11)      NOT NULL AUTO_INCREMENT,
  `Nome_ruolo`  varchar(50)  NOT NULL,
  `Descrizione` text         DEFAULT NULL,
  `Attivo`      tinyint(1)   DEFAULT 1,
  PRIMARY KEY (`ID_ruolo`),
  UNIQUE KEY `Nome_ruolo` (`Nome_ruolo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ruoli come insiemi di privilegi'
  AUTO_INCREMENT=4;

-- --------------------------------------------------------

CREATE TABLE `Ruolo_Privilegio` (
  `ID_ruolo`         int(11)   NOT NULL,
  `ID_privilegio`    int(11)   NOT NULL,
  `Data_assegnazione` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID_ruolo`, `ID_privilegio`),
  KEY `idx_ruolo`      (`ID_ruolo`),
  KEY `idx_privilegio` (`ID_privilegio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Definisce i ruoli come insiemi di privilegi';

-- --------------------------------------------------------

CREATE TABLE `Utente_Ruolo` (
  `ID_ruolo`      int(11)      NOT NULL,
  `email_utente`  varchar(100) NOT NULL,
  PRIMARY KEY (`email_utente`, `ID_ruolo`),
  KEY `idx_ruolo` (`ID_ruolo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Assegna ruoli agli utenti';

-- ============================================================
-- DATI
-- ============================================================

INSERT INTO `Privilegi`
  (`ID_privilegio`, `Nome_privilegio`, `Descrizione`, `Risorsa`, `Azione`) VALUES
(1,  'Inserimento contratto',         'Permette di inserire nuovi contratti',               'contratti',  'INSERT'),
(2,  'Inserimento ore',               'Permette di inserire ore lavorate',                  'ore',        'INSERT'),
(3,  'Generazione busta paga senza PDF', 'Permette di generare buste paga senza scaricare PDF', 'buste_paga', 'INSERT'),
(4,  'Download PDF',                  'Permette di scaricare PDF delle buste paga',         'pdf',        'SELECT'),
(5,  'Invio PDF via email',           'Permette di inviare PDF via email',                  'email',      'INSERT'),
(6,  'Archivio buste paga',           'Accesso all\'archivio delle buste paga',             'archivio',   'SELECT'),
(7,  'Confronto tra buste paga',      'Permette di confrontare buste paga',                 'confronto',  'SELECT'),
(8,  'Gestione utenti',               'Gestione degli utenti del sistema',                  'utenti',     'ALL'),
(9,  'Gestione ruoli',                'Gestione dei ruoli',                                 'ruoli',      'ALL'),
(10, 'Gestione privilegi',            'Gestione dei privilegi',                             'privilegi',  'ALL');

-- --------------------------------------------------------

INSERT INTO `Ruoli`
  (`ID_ruolo`, `Nome_ruolo`, `Descrizione`, `Attivo`) VALUES
(1, 'admin',               'Amministratore del sistema con pieni privilegi', 1),
(2, 'utente_abbonato',     'Utente con abbonamento attivo',                  1),
(3, 'utente_non_abbonato', 'Utente senza abbonamento',                       1);

-- --------------------------------------------------------

-- admin (ruolo 1): tutti i privilegi
-- utente_abbonato (ruolo 2): privilegi 1-7
-- utente_non_abbonato (ruolo 3): privilegi 1-3
INSERT INTO `Ruolo_Privilegio`
  (`ID_ruolo`, `ID_privilegio`, `Data_assegnazione`) VALUES
(1, 1,  '2026-01-27 17:33:21'),
(1, 2,  '2026-01-27 17:33:21'),
(1, 3,  '2026-01-27 17:33:21'),
(1, 4,  '2026-01-27 17:33:21'),
(1, 5,  '2026-01-27 17:33:21'),
(1, 6,  '2026-01-27 17:33:21'),
(1, 7,  '2026-01-27 17:33:21'),
(1, 8,  '2026-01-27 17:33:21'),
(1, 9,  '2026-01-27 17:33:21'),
(1, 10, '2026-01-27 17:33:21'),
(2, 1,  '2026-01-27 17:34:50'),
(2, 2,  '2026-01-27 17:34:50'),
(2, 3,  '2026-01-27 17:34:50'),
(2, 4,  '2026-01-27 17:34:50'),
(2, 5,  '2026-01-27 17:34:50'),
(2, 6,  '2026-01-27 17:34:50'),
(2, 7,  '2026-01-27 17:34:50'),
(3, 1,  '2026-01-27 17:34:50'),
(3, 2,  '2026-01-27 17:34:50'),
(3, 3,  '2026-01-27 17:34:50');

-- --------------------------------------------------------

INSERT INTO `Utenti`
  (`ID_utente`, `N_Telefono`, `Email`, `ID_busta`, `Password_hash`) VALUES
(2, '3922566605', 'mattia.corna2007@gmail.com',              NULL, '$2y$10$C9yMB.fzzuiUGIez6kWiCOHW6TBksDhVm9wUrw1/WsHPn.D9PQY.G'),
(3, '3922566605', 'corna.mattia.studente@itispaleocapa.it',  NULL, '$2y$10$W2q5l/EC786G.nhB1DdRU.WDSNAvEdSEO7MrfGuSTx6nSeIRCU4JK'),
(4, '124',        'aaaa@gmail.com',                         NULL, '$2y$10$0pRGhTkD3250WUU1IJx36ebwSDEXpDEpl5xpA35g7RbP6GvmKdsmO');

-- --------------------------------------------------------

INSERT INTO `Utente_Ruolo`
  (`ID_ruolo`, `email_utente`) VALUES
(3, 'aaaa@gmail.com'),
(1, 'corna.mattia.studente@itispaleocapa.it'),
(1, 'mattia.corna2007@gmail.com');

-- --------------------------------------------------------

INSERT INTO `Impostazioni_contratto`
  (`ID_utente`, `tipologia_dipendente`, `Livello_dipendente`,
   `Maggiorazione_notturna`, `Maggiorazione_straordinaria`, `Maggiorazione_festiva`,
   `Maggiorazione_prefestiva`, `Indennita_malattia`, `Indennita_reperibilita`,
   `Indennita_trasferta`, `Tredicesima`, `Quattordicesima`) VALUES
(2, 'Mettalmeccanico', 'C1', 10.00, 53.00, 65.00, 23.00, 98.00, 4.00, 12.00, 'SI', 'SI');

-- --------------------------------------------------------

INSERT INTO `Profilo_contratto`
  (`ID_utente`, `tipologia_dipendente`, `Livello_dipendente`,
   `Maggiorazione_notturna`, `Maggiorazione_straordinaria`, `Maggiorazione_festiva`,
   `Maggiorazione_prefestiva`, `Indennita_malattia`, `Indennita_reperibilita`,
   `Indennita_trasferta`, `Tredicesima`, `Quattordicesima`) VALUES
(2, 'Mettalmeccanico', '1', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'NO', 'NO');

-- ============================================================
-- FOREIGN KEYS
-- ============================================================

ALTER TABLE `Utenti`
  ADD CONSTRAINT `Utenti_ibfk_2`
    FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `Confronta`
  ADD CONSTRAINT `Confronta_ibfk_1`
    FOREIGN KEY (`ID_utente`) REFERENCES `Utenti` (`ID_utente`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Confronta_ibfk_2`
    FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `Ruolo_Privilegio`
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_1`
    FOREIGN KEY (`ID_ruolo`) REFERENCES `Ruoli` (`ID_ruolo`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_2`
    FOREIGN KEY (`ID_privilegio`) REFERENCES `Privilegi` (`ID_privilegio`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `Utente_Ruolo`
  ADD CONSTRAINT `Utente_Ruolo_ibfk_1`
    FOREIGN KEY (`ID_ruolo`) REFERENCES `Ruoli` (`ID_ruolo`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================
-- VISTE — una per ogni caso d'uso del diagramma
-- ============================================================

--
-- UC: Generazione busta paga senza PDF
-- Ruoli: utente_non_abbonato, utente_abbonato, admin
--
CREATE OR REPLACE VIEW `v_generazione_busta_paga` AS
SELECT
  u.`ID_utente`,
  u.`Email`,
  bp.`ID_busta`,
  bp.`Stipendio_lordo`,
  bp.`Stipendio_netto`,
  bp.`Tasse_totali`
FROM `Utenti` u
LEFT JOIN `Busta_paga` bp ON bp.`ID_busta` = u.`ID_busta`;

--
-- UC: Download PDF
-- Ruoli: utente_abbonato, admin
--
CREATE OR REPLACE VIEW `v_download_pdf` AS
SELECT
  u.`ID_utente`,
  u.`Email`,
  u.`N_Telefono`,
  bp.`ID_busta`,
  bp.`Stipendio_lordo`,
  bp.`Stipendio_netto`,
  bp.`Tasse_totali`,
  ic.`tipologia_dipendente`,
  ic.`Livello_dipendente`,
  ic.`Tredicesima`,
  ic.`Quattordicesima`
FROM `Utenti` u
JOIN `Busta_paga` bp
  ON bp.`ID_busta` = u.`ID_busta`
LEFT JOIN `Impostazioni_contratto` ic
  ON ic.`ID_utente` = u.`ID_utente`;

--
-- UC: Invio PDF via email
-- Ruoli: utente_abbonato, admin
--
CREATE OR REPLACE VIEW `v_invio_pdf_email` AS
SELECT
  u.`ID_utente`,
  u.`Email`,
  bp.`ID_busta`,
  bp.`Stipendio_lordo`,
  bp.`Stipendio_netto`,
  bp.`Tasse_totali`
FROM `Utenti` u
JOIN `Busta_paga` bp ON bp.`ID_busta` = u.`ID_busta`;

--
-- UC: Archivio buste paga
-- Ruoli: utente_abbonato, admin
--
CREATE OR REPLACE VIEW `v_archivio_buste_paga` AS
SELECT
  u.`ID_utente`,
  u.`Email`,
  c.`ID_busta`,
  c.`Data_confronto`  AS `Data_archiviazione`,
  bp.`Stipendio_lordo`,
  bp.`Stipendio_netto`,
  bp.`Tasse_totali`
FROM `Confronta` c
JOIN `Utenti`    u  ON u.`ID_utente` = c.`ID_utente`
JOIN `Busta_paga` bp ON bp.`ID_busta` = c.`ID_busta`
ORDER BY c.`Data_confronto` DESC;

--
-- UC: Confronto tra buste paga
-- Ruoli: utente_abbonato, admin
--
CREATE OR REPLACE VIEW `v_confronto_buste_paga` AS
SELECT
  c1.`ID_utente`,
  u.`Email`,
  c1.`ID_busta`            AS `ID_busta_A`,
  bp1.`Stipendio_lordo`    AS `Lordo_A`,
  bp1.`Stipendio_netto`    AS `Netto_A`,
  bp1.`Tasse_totali`       AS `Tasse_A`,
  c1.`Data_confronto`      AS `Data_A`,
  c2.`ID_busta`            AS `ID_busta_B`,
  bp2.`Stipendio_lordo`    AS `Lordo_B`,
  bp2.`Stipendio_netto`    AS `Netto_B`,
  bp2.`Tasse_totali`       AS `Tasse_B`,
  c2.`Data_confronto`      AS `Data_B`,
  (bp1.`Stipendio_lordo` - bp2.`Stipendio_lordo`) AS `Diff_lordo`,
  (bp1.`Stipendio_netto` - bp2.`Stipendio_netto`) AS `Diff_netto`,
  (bp1.`Tasse_totali`    - bp2.`Tasse_totali`)    AS `Diff_tasse`
FROM `Confronta`  c1
JOIN `Confronta`  c2  ON  c2.`ID_utente` = c1.`ID_utente`
                      AND c2.`ID_busta`  > c1.`ID_busta`
JOIN `Utenti`     u   ON  u.`ID_utente`  = c1.`ID_utente`
JOIN `Busta_paga` bp1 ON bp1.`ID_busta`  = c1.`ID_busta`
JOIN `Busta_paga` bp2 ON bp2.`ID_busta`  = c2.`ID_busta`;

--
-- UC: Gestione utenti (ADMIN)
--
CREATE OR REPLACE VIEW `v_gestione_utenti` AS
SELECT
  u.`ID_utente`,
  u.`Email`,
  u.`N_Telefono`,
  r.`ID_ruolo`,
  r.`Nome_ruolo`,
  r.`Attivo` AS `Ruolo_attivo`
FROM `Utenti` u
LEFT JOIN `Utente_Ruolo` ur ON ur.`email_utente` = u.`Email`
LEFT JOIN `Ruoli`         r  ON r.`ID_ruolo`      = ur.`ID_ruolo`;

--
-- UC: Gestione ruoli (ADMIN)
--
CREATE OR REPLACE VIEW `v_gestione_ruoli` AS
SELECT
  r.`ID_ruolo`,
  r.`Nome_ruolo`,
  r.`Descrizione`  AS `Descrizione_ruolo`,
  r.`Attivo`,
  p.`ID_privilegio`,
  p.`Nome_privilegio`,
  p.`Risorsa`,
  p.`Azione`
FROM `Ruoli` r
JOIN `Ruolo_Privilegio` rp ON rp.`ID_ruolo`     = r.`ID_ruolo`
JOIN `Privilegi`         p  ON p.`ID_privilegio` = rp.`ID_privilegio`
WHERE r.`Attivo` = 1;

--
-- UC: Gestione privilegi (ADMIN)
--
CREATE OR REPLACE VIEW `v_gestione_privilegi` AS
SELECT
  p.`ID_privilegio`,
  p.`Nome_privilegio`,
  p.`Descrizione`,
  p.`Risorsa`,
  p.`Azione`,
  r.`ID_ruolo`,
  r.`Nome_ruolo`
FROM `Privilegi` p
LEFT JOIN `Ruolo_Privilegio` rp ON rp.`ID_privilegio` = p.`ID_privilegio`
LEFT JOIN `Ruoli`             r  ON r.`ID_ruolo`       = rp.`ID_ruolo`
ORDER BY p.`ID_privilegio`, r.`ID_ruolo`;

-- ============================================================

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
