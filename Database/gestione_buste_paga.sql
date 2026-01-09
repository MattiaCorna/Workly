-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 09, 2026 alle 10:16
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
-- Database: `gestione_buste_paga`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `busta_paga`
--

CREATE TABLE `busta_paga` (
  `id_busta` int(11) NOT NULL,
  `stipendio_lordo` decimal(10,2) DEFAULT NULL,
  `stipendio_netto` decimal(10,2) DEFAULT NULL,
  `tasse_totali` decimal(10,2) DEFAULT NULL,
  `id_utente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `confronto`
--

CREATE TABLE `confronto` (
  `id_confronto` int(11) NOT NULL,
  `id_utente_abbonato` int(11) NOT NULL,
  `id_busta_1` int(11) NOT NULL,
  `id_busta_2` int(11) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Struttura della tabella `profilo_contratto`
--

CREATE TABLE `profilo_contratto` (
  `id_profilo` int(11) NOT NULL,
  `maggioranze` varchar(100) DEFAULT NULL,
  `livello_dipendente` varchar(50) DEFAULT NULL,
  `mese_lavorativo` varchar(50) DEFAULT NULL,
  `id_utente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id_utente` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `abbonato` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_telefono`
--

CREATE TABLE `utente_telefono` (
  `id_utente` int(11) NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `busta_paga`
--
ALTER TABLE `busta_paga`
  ADD PRIMARY KEY (`id_busta`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `confronto`
--
ALTER TABLE `confronto`
  ADD PRIMARY KEY (`id_confronto`),
  ADD KEY `id_utente_abbonato` (`id_utente_abbonato`),
  ADD KEY `id_busta_1` (`id_busta_1`),
  ADD KEY `id_busta_2` (`id_busta_2`);

--
-- Indici per le tabelle `profilo_contratto`
--
ALTER TABLE `profilo_contratto`
  ADD PRIMARY KEY (`id_profilo`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `utente_telefono`
--
ALTER TABLE `utente_telefono`
  ADD PRIMARY KEY (`id_utente`,`telefono`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `busta_paga`
--
ALTER TABLE `busta_paga`
  MODIFY `id_busta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `confronto`
--
ALTER TABLE `confronto`
  MODIFY `id_confronto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `profilo_contratto`
--
ALTER TABLE `profilo_contratto`
  MODIFY `id_profilo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id_utente` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `busta_paga`
--
ALTER TABLE `busta_paga`
  ADD CONSTRAINT `busta_paga_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE;

--
-- Limiti per la tabella `confronto`
--
ALTER TABLE `confronto`
  ADD CONSTRAINT `confronto_ibfk_1` FOREIGN KEY (`id_utente_abbonato`) REFERENCES `utente` (`id_utente`),
  ADD CONSTRAINT `confronto_ibfk_2` FOREIGN KEY (`id_busta_1`) REFERENCES `busta_paga` (`id_busta`),
  ADD CONSTRAINT `confronto_ibfk_3` FOREIGN KEY (`id_busta_2`) REFERENCES `busta_paga` (`id_busta`);

--
-- Limiti per la tabella `profilo_contratto`
--
ALTER TABLE `profilo_contratto`
  ADD CONSTRAINT `profilo_contratto_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE;

--
-- Limiti per la tabella `utente_telefono`
--
ALTER TABLE `utente_telefono`
  ADD CONSTRAINT `utente_telefono_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
