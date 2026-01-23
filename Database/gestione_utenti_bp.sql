-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 23, 2026 alle 08:42
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

DELIMITER $$
--
-- Procedure
--
CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Aggiungi_Privilegio_A_Ruolo` (IN `p_ID_ruolo` INT, IN `p_ID_privilegio` INT)   BEGIN
    INSERT INTO Ruolo_Privilegio (ID_ruolo, ID_privilegio)
    VALUES (p_ID_ruolo, p_ID_privilegio)
    ON DUPLICATE KEY UPDATE Data_assegnazione = CURRENT_TIMESTAMP;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Assegna_Ruolo` (IN `p_ID_utente` INT, IN `p_ID_ruolo` INT, IN `p_Assegnato_da` INT)   BEGIN
    INSERT INTO Utente_Ruolo (ID_utente, ID_ruolo, Assegnato_da)
    VALUES (p_ID_utente, p_ID_ruolo, p_Assegnato_da)
    ON DUPLICATE KEY UPDATE 
        Data_assegnazione = CURRENT_TIMESTAMP,
        Assegnato_da = p_Assegnato_da;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Ottieni_Definizione_Ruolo` (IN `p_ID_ruolo` INT)   BEGIN
    SELECT 
        r.Nome_ruolo,
        r.Descrizione AS Descrizione_ruolo,
        p.Nome_privilegio,
        p.Risorsa,
        p.Azione,
        p.Descrizione AS Descrizione_privilegio
    FROM Ruoli r
    JOIN Ruolo_Privilegio rp ON r.ID_ruolo = rp.ID_ruolo
    JOIN Privilegi p ON rp.ID_privilegio = p.ID_privilegio
    WHERE r.ID_ruolo = p_ID_ruolo
    ORDER BY p.Risorsa, p.Azione;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Ottieni_Privilegi_Utente` (IN `p_ID_utente` INT)   BEGIN
    SELECT DISTINCT
        p.Nome_privilegio,
        p.Risorsa,
        p.Azione,
        p.Descrizione,
        r.Nome_ruolo
    FROM Utente_Ruolo ur
    JOIN Ruolo_Privilegio rp ON ur.ID_ruolo = rp.ID_ruolo
    JOIN Privilegi p ON rp.ID_privilegio = p.ID_privilegio
    JOIN Ruoli r ON ur.ID_ruolo = r.ID_ruolo
    WHERE ur.ID_utente = p_ID_utente
    AND r.Attivo = TRUE
    ORDER BY p.Risorsa, p.Azione;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Rimuovi_Privilegio_Da_Ruolo` (IN `p_ID_ruolo` INT, IN `p_ID_privilegio` INT)   BEGIN
    DELETE FROM Ruolo_Privilegio
    WHERE ID_ruolo = p_ID_ruolo AND ID_privilegio = p_ID_privilegio;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Rimuovi_Ruolo` (IN `p_ID_utente` INT, IN `p_ID_ruolo` INT)   BEGIN
    DELETE FROM Utente_Ruolo
    WHERE ID_utente = p_ID_utente AND ID_ruolo = p_ID_ruolo;
END$$

CREATE DEFINER=`utente_phpmyadmin`@`localhost` PROCEDURE `Verifica_Privilegio_Utente` (IN `p_ID_utente` INT, IN `p_Risorsa` VARCHAR(100), IN `p_Azione` VARCHAR(20), OUT `p_Ha_Privilegio` BOOLEAN)   BEGIN
    SELECT COUNT(*) > 0 INTO p_Ha_Privilegio
    FROM Utente_Ruolo ur
    JOIN Ruolo_Privilegio rp ON ur.ID_ruolo = rp.ID_ruolo
    JOIN Privilegi p ON rp.ID_privilegio = p.ID_privilegio
    JOIN Ruoli r ON ur.ID_ruolo = r.ID_ruolo
    WHERE ur.ID_utente = p_ID_utente 
    AND p.Risorsa = p_Risorsa
    AND (p.Azione = p_Azione OR p.Azione = 'ALL')
    AND r.Attivo = TRUE;
END$$

DELIMITER ;

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

--
-- Trigger `Confronta`
--
DELIMITER $$
CREATE TRIGGER `check_confronta_abbonato` BEFORE INSERT ON `Confronta` FOR EACH ROW BEGIN
    DECLARE v_tipo VARCHAR(20);
    
    SELECT Tipo_utente INTO v_tipo
    FROM Utenti
    WHERE ID_utente = NEW.ID_utente;
    
    IF v_tipo != 'abbonato' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Solo gli utenti abbonati possono confrontare buste paga';
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Struttura della tabella `Ruolo_Privilegio`
--

CREATE TABLE `Ruolo_Privilegio` (
  `ID_ruolo` int(11) NOT NULL,
  `ID_privilegio` int(11) NOT NULL,
  `Data_assegnazione` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Definisce i ruoli come insiemi di privilegi';

-- --------------------------------------------------------

--
-- Struttura della tabella `Utente_Ruolo`
--

CREATE TABLE `Utente_Ruolo` (
  `ID_utente` int(11) NOT NULL,
  `ID_ruolo` int(11) NOT NULL,
  `Data_assegnazione` timestamp NULL DEFAULT current_timestamp(),
  `Assegnato_da` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Assegna ruoli agli utenti';

-- --------------------------------------------------------

--
-- Struttura della tabella `Utenti`
--

CREATE TABLE `Utenti` (
  `ID_utente` int(11) NOT NULL,
  `N_Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Tipo_utente` enum('abbonato','non_abbonato') DEFAULT NULL,
  `ID_profilo` int(11) NOT NULL,
  `ID_busta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Utenti del sistema (gerarchia collassata)';

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
`ID_utente` int(11)
,`Email` varchar(100)
,`N_Telefono` varchar(20)
,`Tipo_utente` enum('abbonato','non_abbonato')
,`ID_profilo` int(11)
,`Livello_dipendente` varchar(50)
,`Maggiorazioni` decimal(10,2)
,`Mese_lavorativo` int(11)
,`ID_busta` int(11)
,`Stipendio_lordo` decimal(10,2)
,`Stipendio_netto` decimal(10,2)
,`Tasse_totali` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `Vista_Privilegi_Utente`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `Vista_Privilegi_Utente` (
`ID_utente` int(11)
,`Email` varchar(100)
,`Tipo_utente` enum('abbonato','non_abbonato')
,`Nome_ruolo` varchar(50)
,`Nome_privilegio` varchar(100)
,`Risorsa` varchar(100)
,`Azione` enum('SELECT','INSERT','UPDATE','DELETE','ALL')
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `Vista_Utenti_Ruoli`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `Vista_Utenti_Ruoli` (
`ID_utente` int(11)
,`Email` varchar(100)
,`N_Telefono` varchar(20)
,`Tipo_utente` enum('abbonato','non_abbonato')
,`Nome_ruolo` varchar(50)
,`Descrizione_ruolo` text
,`Data_assegnazione` timestamp
,`Assegnato_da_email` varchar(100)
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
  ADD PRIMARY KEY (`ID_utente`,`ID_ruolo`),
  ADD KEY `Assegnato_da` (`Assegnato_da`),
  ADD KEY `idx_utente` (`ID_utente`),
  ADD KEY `idx_ruolo` (`ID_ruolo`);

--
-- Indici per le tabelle `Utenti`
--
ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`ID_utente`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `ID_profilo` (`ID_profilo`),
  ADD KEY `ID_busta` (`ID_busta`),
  ADD KEY `idx_tipo_utente` (`Tipo_utente`),
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
  MODIFY `ID_privilegio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Profilo_contratto`
--
ALTER TABLE `Profilo_contratto`
  MODIFY `ID_profilo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Ruoli`
--
ALTER TABLE `Ruoli`
  MODIFY `ID_ruolo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  MODIFY `ID_utente` int(11) NOT NULL AUTO_INCREMENT;

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

-- --------------------------------------------------------

--
-- Struttura per vista `Vista_Privilegi_Utente`
--
DROP TABLE IF EXISTS `Vista_Privilegi_Utente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `Vista_Privilegi_Utente`  AS SELECT DISTINCT `u`.`ID_utente` AS `ID_utente`, `u`.`Email` AS `Email`, `u`.`Tipo_utente` AS `Tipo_utente`, `r`.`Nome_ruolo` AS `Nome_ruolo`, `p`.`Nome_privilegio` AS `Nome_privilegio`, `p`.`Risorsa` AS `Risorsa`, `p`.`Azione` AS `Azione` FROM ((((`Utenti` `u` join `Utente_Ruolo` `ur` on(`u`.`ID_utente` = `ur`.`ID_utente`)) join `Ruoli` `r` on(`ur`.`ID_ruolo` = `r`.`ID_ruolo`)) join `Ruolo_Privilegio` `rp` on(`r`.`ID_ruolo` = `rp`.`ID_ruolo`)) join `Privilegi` `p` on(`rp`.`ID_privilegio` = `p`.`ID_privilegio`)) WHERE `r`.`Attivo` = 1 ORDER BY `u`.`ID_utente` ASC, `p`.`Risorsa` ASC, `p`.`Azione` ASC ;

-- --------------------------------------------------------

--
-- Struttura per vista `Vista_Utenti_Ruoli`
--
DROP TABLE IF EXISTS `Vista_Utenti_Ruoli`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `Vista_Utenti_Ruoli`  AS SELECT `u`.`ID_utente` AS `ID_utente`, `u`.`Email` AS `Email`, `u`.`N_Telefono` AS `N_Telefono`, `u`.`Tipo_utente` AS `Tipo_utente`, `r`.`Nome_ruolo` AS `Nome_ruolo`, `r`.`Descrizione` AS `Descrizione_ruolo`, `ur`.`Data_assegnazione` AS `Data_assegnazione`, `ua`.`Email` AS `Assegnato_da_email` FROM (((`Utenti` `u` left join `Utente_Ruolo` `ur` on(`u`.`ID_utente` = `ur`.`ID_utente`)) left join `Ruoli` `r` on(`ur`.`ID_ruolo` = `r`.`ID_ruolo`)) left join `Utenti` `ua` on(`ur`.`Assegnato_da` = `ua`.`ID_utente`)) ORDER BY `u`.`ID_utente` ASC, `r`.`Nome_ruolo` ASC ;

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
-- Limiti per la tabella `Utente_Ruolo`
--
ALTER TABLE `Utente_Ruolo`
  ADD CONSTRAINT `Utente_Ruolo_ibfk_1` FOREIGN KEY (`ID_utente`) REFERENCES `Utenti` (`ID_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Utente_Ruolo_ibfk_2` FOREIGN KEY (`ID_ruolo`) REFERENCES `Ruoli` (`ID_ruolo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Utente_Ruolo_ibfk_3` FOREIGN KEY (`Assegnato_da`) REFERENCES `Utenti` (`ID_utente`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  ADD CONSTRAINT `Utenti_ibfk_1` FOREIGN KEY (`ID_profilo`) REFERENCES `Profilo_contratto` (`ID_profilo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Utenti_ibfk_2` FOREIGN KEY (`ID_busta`) REFERENCES `Busta_paga` (`ID_busta`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
