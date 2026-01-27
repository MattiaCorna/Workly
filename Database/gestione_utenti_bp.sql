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

-- --------------------------------------------------------

--
-- Struttura della tabella `Busta_paga`
--

CREATE TABLE `Busta_paga` (
  `ID_busta` int(11) NOT NULL,
  `Stipendio_lordo` decimal(10,2) NOT NULL,
  `Stipendio_netto` decimal(10,2) NOT NULL,
  `Tasse_totali` decimal(10,2) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Struttura della tabella `Confronta`
--

CREATE TABLE `Confronta` (
  `ID_utente` int(11) NOT NULL,
  `ID_busta` int(11) NOT NULL,
  `Data_confronto` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Confronti tra buste paga (solo utenti abbonati)';

-- --------------------------------------------------------

--
-- Struttura della tabella `Privilegi`
--

CREATE TABLE `Privilegi` (
  `ID_privilegio` int(11) NOT NULL,
  `Nome_privilegio` varchar(100) NOT NULL,
  `Descrizione` text DEFAULT NULL,
  `Risorsa` varchar(100) NOT NULL,
  `Azione` enum('SELECT','INSERT','UPDATE','DELETE','ALL') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Privilegi atomici del sistema';

--
-- Dump dei dati per la tabella `Privilegi`
--

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

--
-- Struttura della tabella `Profilo_contratto`
--

CREATE TABLE `Profilo_contratto` (
  `ID_profilo` int(11) NOT NULL,
  `Maggiorazioni` decimal(10,2) DEFAULT NULL,
  `Livello_dipendente` varchar(50) NOT NULL,
  `Mese_lavorativo` int(11) DEFAULT NULL CHECK (`Mese_lavorativo` between 1 and 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profili contrattuali dei dipendenti';

--
-- Dump dei dati per la tabella `Profilo_contratto`
--

INSERT INTO `Profilo_contratto` (`ID_profilo`, `Maggiorazioni`, `Livello_dipendente`, `Mese_lavorativo`) VALUES
(1, 0.00, 'Base', 1),
(2, NULL, 'base', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `Ruoli`
--

CREATE TABLE `Ruoli` (
  `ID_ruolo` int(11) NOT NULL,
  `Nome_ruolo` varchar(50) NOT NULL,
  `Descrizione` text DEFAULT NULL,
  `Attivo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ruoli come insiemi di privilegi';

--
-- Dump dei dati per la tabella `Ruoli`
--

INSERT INTO `Ruoli` (`ID_ruolo`, `Nome_ruolo`, `Descrizione`, `Attivo`) VALUES
(1, 'admin', 'Amministratore del sistema con pieni privilegi', 1),
(2, 'utente_abbonato', 'Utente con abbonamento attivo', 1),
(3, 'utente_non_abbonato', 'Utente senza abbonamento', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `Ruolo_Privilegio`
--

CREATE TABLE `Ruolo_Privilegio` (
  `ID_ruolo` int(11) NOT NULL,
  `ID_privilegio` int(11) NOT NULL,
  `Data_assegnazione` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Definisce i ruoli come insiemi di privilegi';

--
-- Dump dei dati per la tabella `Ruolo_Privilegio`
--

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

--
-- Struttura della tabella `Utente_Ruolo`
--

CREATE TABLE `Utente_Ruolo` (
  `ID_ruolo` int(11) NOT NULL,
  `email_utente` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Assegna ruoli agli utenti';

--
-- Dump dei dati per la tabella `Utente_Ruolo`
--

INSERT INTO `Utente_Ruolo` (`ID_ruolo`, `email_utente`) VALUES
(3, 'aaaa@gmail.com'),
(1, 'corna.mattia.studente@itispaleocapa.it'),
(1, 'mattia.corna2007@gmail.com');

-- --------------------------------------------------------

--
-- Struttura della tabella `Utenti`
--

CREATE TABLE `Utenti` (
  `ID_utente` int(11) NOT NULL,
  `N_Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `ID_busta` int(11) DEFAULT NULL,
  `Password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Utenti del sistema (gerarchia collassata)';

--
-- Dump dei dati per la tabella `Utenti`
--

INSERT INTO `Utenti` (`ID_utente`, `N_Telefono`, `Email`, `ID_busta`, `Password_hash`) VALUES
(2, '3922566605', 'mattia.corna2007@gmail.com', NULL, '$2y$10$C9yMB.fzzuiUGIez6kWiCOHW6TBksDhVm9wUrw1/WsHPn.D9PQY.G'),
(3, '3922566605', 'corna.mattia.studente@itispaleocapa.it', NULL, '$2y$10$W2q5l/EC786G.nhB1DdRU.WDSNAvEdSEO7MrfGuSTx6nSeIRCU4JK'),
(4, '124', 'aaaa@gmail.com', NULL, '$2y$10$0pRGhTkD3250WUU1IJx36ebwSDEXpDEpl5xpA35g7RbP6GvmKdsmO');

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `Vista_Definizione_Ruoli`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `Vista_Definizione_Ruoli` (
`ID_ruolo` int(11)
,`Nome_ruolo` varchar(50)
,`Descrizione_ruolo` text
,`Attivo` tinyint(1)
,`ID_privilegio` int(11)
,`Nome_privilegio` varchar(100)
,`Descrizione_privilegio` text
,`Risorsa` varchar(100)
,`Azione` enum('SELECT','INSERT','UPDATE','DELETE','ALL')
,`Privilegio_aggiunto_il` timestamp
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `Vista_Dettaglio_Utente`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `Vista_Dettaglio_Utente` (
);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Busta_paga`
--
ALTER TABLE `Busta_paga`
  ADD PRIMARY KEY (`ID_busta`);

--
-- Indici per le tabelle `Confronta`
--
ALTER TABLE `Confronta`
  ADD PRIMARY KEY (`ID_utente`,`ID_busta`),
  ADD KEY `idx_confronta_utente` (`ID_utente`),
  ADD KEY `idx_confronta_busta` (`ID_busta`),
  ADD KEY `idx_confronta_data` (`Data_confronto`);

--
-- Indici per le tabelle `Privilegi`
--
ALTER TABLE `Privilegi`
  ADD PRIMARY KEY (`ID_privilegio`),
  ADD UNIQUE KEY `Nome_privilegio` (`Nome_privilegio`),
  ADD KEY `idx_risorsa_azione` (`Risorsa`,`Azione`);

--
-- Indici per le tabelle `Profilo_contratto`
--
ALTER TABLE `Profilo_contratto`
  ADD PRIMARY KEY (`ID_profilo`);

--
-- Indici per le tabelle `Ruoli`
--
ALTER TABLE `Ruoli`
  ADD PRIMARY KEY (`ID_ruolo`),
  ADD UNIQUE KEY `Nome_ruolo` (`Nome_ruolo`);

--
-- Indici per le tabelle `Ruolo_Privilegio`
--
ALTER TABLE `Ruolo_Privilegio`
  ADD PRIMARY KEY (`ID_ruolo`,`ID_privilegio`),
  ADD KEY `idx_ruolo` (`ID_ruolo`),
  ADD KEY `idx_privilegio` (`ID_privilegio`);

--
-- Indici per le tabelle `Utente_Ruolo`
--
ALTER TABLE `Utente_Ruolo`
  ADD PRIMARY KEY (`email_utente`,`ID_ruolo`),
  ADD KEY `idx_ruolo` (`ID_ruolo`);

--
-- Indici per le tabelle `Utenti`
--
ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`ID_utente`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `ID_busta` (`ID_busta`),
  ADD KEY `idx_email` (`Email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Busta_paga`
--
ALTER TABLE `Busta_paga`
  MODIFY `ID_busta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Privilegi`
--
ALTER TABLE `Privilegi`
  MODIFY `ID_privilegio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `Profilo_contratto`
--
ALTER TABLE `Profilo_contratto`
  MODIFY `ID_profilo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `Ruoli`
--
ALTER TABLE `Ruoli`
  MODIFY `ID_ruolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  MODIFY `ID_utente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Struttura per vista `Vista_Definizione_Ruoli`
--
DROP TABLE IF EXISTS `Vista_Definizione_Ruoli`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `Vista_Definizione_Ruoli`  AS SELECT `r`.`ID_ruolo` AS `ID_ruolo`, `r`.`Nome_ruolo` AS `Nome_ruolo`, `r`.`Descrizione` AS `Descrizione_ruolo`, `r`.`Attivo` AS `Attivo`, `p`.`ID_privilegio` AS `ID_privilegio`, `p`.`Nome_privilegio` AS `Nome_privilegio`, `p`.`Descrizione` AS `Descrizione_privilegio`, `p`.`Risorsa` AS `Risorsa`, `p`.`Azione` AS `Azione`, `rp`.`Data_assegnazione` AS `Privilegio_aggiunto_il` FROM ((`Ruoli` `r` join `Ruolo_Privilegio` `rp` on(`r`.`ID_ruolo` = `rp`.`ID_ruolo`)) join `Privilegi` `p` on(`rp`.`ID_privilegio` = `p`.`ID_privilegio`)) ORDER BY `r`.`Nome_ruolo` ASC, `p`.`Risorsa` ASC, `p`.`Azione` ASC ;

-- --------------------------------------------------------

--
-- Struttura per vista `Vista_Dettaglio_Utente`
--
DROP TABLE IF EXISTS `Vista_Dettaglio_Utente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `Vista_Dettaglio_Utente`  AS SELECT `u`.`ID_utente` AS `ID_utente`, `u`.`Email` AS `Email`, `u`.`N_Telefono` AS `N_Telefono`, `u`.`Tipo_utente` AS `Tipo_utente`, `pc`.`ID_profilo` AS `ID_profilo`, `pc`.`Livello_dipendente` AS `Livello_dipendente`, `pc`.`Maggiorazioni` AS `Maggiorazioni`, `pc`.`Mese_lavorativo` AS `Mese_lavorativo`, `bp`.`ID_busta` AS `ID_busta`, `bp`.`Stipendio_lordo` AS `Stipendio_lordo`, `bp`.`Stipendio_netto` AS `Stipendio_netto`, `bp`.`Tasse_totali` AS `Tasse_totali` FROM ((`Utenti` `u` join `Profilo_contratto` `pc` on(`u`.`ID_profilo` = `pc`.`ID_profilo`)) left join `Busta_paga` `bp` on(`u`.`ID_busta` = `bp`.`ID_busta`)) ;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Confronta`
--
ALTER TABLE `Confronta`
  ADD CONSTRAINT `Confronta_ibfk_1` FOREIGN KEY (`ID_utente`) REFERENCES `Utenti` (`ID_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Confronta_ibfk_2` FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Ruolo_Privilegio`
--
ALTER TABLE `Ruolo_Privilegio`
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_1` FOREIGN KEY (`ID_ruolo`) REFERENCES `Ruoli` (`ID_ruolo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Ruolo_Privilegio_ibfk_2` FOREIGN KEY (`ID_privilegio`) REFERENCES `Privilegi` (`ID_privilegio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  ADD CONSTRAINT `Utenti_ibfk_2` FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
