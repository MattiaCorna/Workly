-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 27, 2026 alle 17:55
-- Versione del server: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versione PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestione_utenti_bp`
--

CREATE DATABASE IF NOT EXISTS `gestione_utenti_bp`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `gestione_utenti_bp`;

-- --------------------------------------------------------
-- Struttura della tabella `Busta_paga`
-- --------------------------------------------------------

CREATE TABLE `Busta_paga` (
  `ID_busta` int(11) NOT NULL,
  `Stipendio_lordo` decimal(10,2) NOT NULL,
  `Stipendio_netto` decimal(10,2) NOT NULL,
  `Tasse_totali` decimal(10,2) NOT NULL
);

-- --------------------------------------------------------
-- Struttura della tabella `Confronta`
-- --------------------------------------------------------

CREATE TABLE `Confronta` (
  `ID_utente` int(11) NOT NULL,
  `ID_busta` int(11) NOT NULL,
  `Data_confronto` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Confronti tra buste paga (solo utenti abbonati)';

-- --------------------------------------------------------
-- Struttura della tabella `Privilegi`
-- --------------------------------------------------------

CREATE TABLE `Privilegi` (
  `ID_privilegio` int(11) NOT NULL,
  `Nome_privilegio` varchar(100) NOT NULL,
  `Descrizione` text DEFAULT NULL,
  `Risorsa` varchar(100) NOT NULL,
  `Azione` enum('SELECT','INSERT','UPDATE','DELETE','ALL') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Privilegi atomici del sistema';

INSERT INTO `Privilegi` (`ID_privilegio`, `Nome_privilegio`, `Descrizione`, `Risorsa`, `Azione`) VALUES
(1, 'Inserimento contratto', 'Permette di inserire nuovi contratti', 'contratti', 'INSERT'),
(2, 'Inserimento ore', 'Permette di inserire ore lavorate', 'ore', 'INSERT'),
(3, 'Generazione busta paga senza PDF', 'Permette di generare buste paga senza scaricare PDF', 'buste_paga', 'INSERT'),
(4, 'Download PDF', 'Permette di scaricare PDF delle buste paga', 'pdf', 'SELECT'),
(5, 'Invio PDF via email', 'Permette di inviare PDF via email', 'email', 'INSERT'),
(6, 'Archivio buste paga', 'Accesso all archivio delle buste paga', 'archivio', 'SELECT'),
(7, 'Confronto tra buste paga', 'Permette di confrontare buste paga', 'confronto', 'SELECT'),
(8, 'Gestione utenti', 'Gestione degli utenti del sistema', 'utenti', 'ALL'),
(9, 'Gestione ruoli', 'Gestione dei ruoli', 'ruoli', 'ALL'),
(10, 'Gestione privilegi', 'Gestione dei privilegi', 'privilegi', 'ALL');

-- --------------------------------------------------------
-- Struttura della tabella `Profilo_contratto` (MODIFICATA)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Profilo_contratto`;

CREATE TABLE `Profilo_contratto` (
  `ID_profilo` int(11) NOT NULL,
  `Livello_dipendente` tinyint(1) NOT NULL,
  `Maggiorazione_notturna` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Maggiorazione_straordinaria` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Maggiorazione_festiva` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Maggiorazione_prefestiva` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Indennita_malattia` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Indennita_reperibilita` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '€/ora',
  `Indennita_trasferta` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '€/ora',
  `Tredicesima` enum('SI','NO') NOT NULL DEFAULT 'NO',
  `Quattordicesima` enum('SI','NO') NOT NULL DEFAULT 'NO',
  CHECK (`Livello_dipendente` BETWEEN 1 AND 7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Profili contrattuali dei dipendenti';

-- (Se vuoi, qui puoi aggiungere INSERT dei profili 1..7)

-- --------------------------------------------------------
-- Struttura della tabella `Ruoli`
-- --------------------------------------------------------

CREATE TABLE `Ruoli` (
  `ID_ruolo` int(11) NOT NULL,
  `Nome_ruolo` varchar(50) NOT NULL,
  `Descrizione` text DEFAULT NULL,
  `Attivo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ruoli come insiemi di privilegi';

INSERT INTO `Ruoli` (`ID_ruolo`, `Nome_ruolo`, `Descrizione`, `Attivo`) VALUES
(1, 'admin', 'Amministratore del sistema con pieni privilegi', 1),
(2, 'utente_abbonato', 'Utente con abbonamento attivo', 1),
(3, 'utente_non_abbonato', 'Utente senza abbonamento', 1);

-- --------------------------------------------------------
-- Struttura della tabella `Ruolo_Privilegio`
-- --------------------------------------------------------

CREATE TABLE `Ruolo_Privilegio` (
  `ID_ruolo` int(11) NOT NULL,
  `ID_privilegio` int(11) NOT NULL,
  `Data_assegnazione` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Definisce i ruoli come insiemi di privilegi';

INSERT INTO `Ruolo_Privilegio` (`ID_ruolo`, `ID_privilegio`, `Data_assegnazione`) VALUES
(1, 1, '2026-01-27 17:33:21'),
(1, 2, '2026-01-27 17:33:21'),
(1, 3, '2026-01-27 17:33:21'),
(1, 4, '2026-01-27 17:33:21'),
(1, 5, '2026-01-27 17:33:21'),
(1, 6, '2026-01-27 17:33:21'),
(1, 7, '2026-01-27 17:33:21'),
(1, 8, '2026-01-27 17:33:21'),
(1, 9, '2026-01-27 17:33:21'),
(1, 10, '2026-01-27 17:33:21'),
(2, 1, '2026-01-27 17:34:50'),
(2, 2, '2026-01-27 17:34:50'),
(2, 3, '2026-01-27 17:34:50'),
(2, 4, '2026-01-27 17:34:50'),
(2, 5, '2026-01-27 17:34:50'),
(2, 6, '2026-01-27 17:34:50'),
(2, 7, '2026-01-27 17:34:50'),
(3, 1, '2026-01-27 17:34:50'),
(3, 2, '2026-01-27 17:34:50'),
(3, 3, '2026-01-27 17:34:50');

-- --------------------------------------------------------
-- Struttura della tabella `Utente_Ruolo`
-- --------------------------------------------------------

CREATE TABLE `Utente_Ruolo` (
  `ID_ruolo` int(11) NOT NULL,
  `email_utente` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Assegna ruoli agli utenti';

INSERT INTO `Utente_Ruolo` (`ID_ruolo`, `email_utente`) VALUES
(3, 'aaaa@gmail.com'),
(1, 'corna.mattia.studente@itispaleocapa.it'),
(1, 'mattia.corna2007@gmail.com');

-- --------------------------------------------------------
-- Struttura della tabella `Utenti`
-- --------------------------------------------------------

CREATE TABLE `Utenti` (
  `ID_utente` int(11) NOT NULL,
  `N_Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `ID_busta` int(11) DEFAULT NULL,
  `Password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Utenti del sistema (gerarchia collassata)';

INSERT INTO `Utenti` (`ID_utente`, `N_Telefono`, `Email`, `ID_busta`, `Password_hash`) VALUES
(2, '3922566605', 'mattia.corna2007@gmail.com', NULL, '$2y$10$C9yMB.fzzuiUGIez6kWiCOHW6TBksDhVm9wUrw1/WsHPn.D9PQY.G'),
(3, '3922566605', 'corna.mattia.studente@itispaleocapa.it', NULL, '$2y$10$W2q5l/EC786G.nhB1DdRU.WDSNAvEdSEO7MrfGuSTx6nSeIRCU4JK'),
(4, '124', 'aaaa@gmail.com', NULL, '$2y$10$0pRGhTkD3250WUU1IJx36ebwSDEXpDEpl5xpA35g7RbP6GvmKdsmO');

-- --------------------------------------------------------
-- Indici
-- --------------------------------------------------------

ALTER TABLE `Busta_paga`
  ADD PRIMARY KEY (`ID_busta`);

ALTER TABLE `Confronta`
  ADD PRIMARY KEY (`ID_utente`,`ID_busta`),
  ADD KEY `idx_confronta_utente` (`ID_utente`),
  ADD KEY `idx_confronta_busta` (`ID_busta`),
  ADD KEY `idx_confronta_data` (`Data_confronto`);

ALTER TABLE `Privilegi`
  ADD PRIMARY KEY (`ID_privilegio`),
  ADD UNIQUE KEY `Nome_privilegio` (`Nome_privilegio`),
  ADD KEY `idx_risorsa_azione` (`Risorsa`,`Azione`);

ALTER TABLE `Profilo_contratto`
  ADD PRIMARY KEY (`ID_profilo`);

ALTER TABLE `Ruoli`
  ADD PRIMARY KEY (`ID_ruolo`),
  ADD UNIQUE KEY `Nome_ruolo` (`Nome_ruolo`);

ALTER TABLE `Ruolo_Privilegio`
  ADD PRIMARY KEY (`ID_ruolo`,`ID_privilegio`),
  ADD KEY `idx_ruolo` (`ID_ruolo`),
  ADD KEY `idx_privilegio` (`ID_privilegio`);

ALTER TABLE `Utente_Ruolo`
  ADD PRIMARY KEY (`email_utente`,`ID_ruolo`),
  ADD KEY `idx_ruolo` (`ID_ruolo`);

ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`ID_utente`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `ID_busta` (`ID_busta`),
  ADD KEY `idx_email` (`Email`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------

ALTER TABLE `Busta_paga`
  MODIFY `ID_busta` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Privilegi`
  MODIFY `ID_privilegio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `Profilo_contratto`
  MODIFY `ID_profilo` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Ruoli`
  MODIFY `ID_ruolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `Utenti`
  MODIFY `ID_utente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------
-- Vincoli (FK)
-- --------------------------------------------------------

ALTER TABLE `Confronta`
  ADD CONSTRAINT `Confronta_ibfk_1` FOREIGN KEY (`ID_utente`) REFERENCES `Utenti` (`ID_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Confronta_ibfk_2` FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `Ruolo_Privilegio`
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_1` FOREIGN KEY (`ID_ruolo`) REFERENCES `Ruoli` (`ID_ruolo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_2` FOREIGN KEY (`ID_privilegio`) REFERENCES `Privilegi` (`ID_privilegio`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `Utenti`
  ADD CONSTRAINT `Utenti_ibfk_2` FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
