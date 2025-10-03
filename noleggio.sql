-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Ott 03, 2025 alle 23:39
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `noleggio`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `autista`
--

CREATE TABLE `autista` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `patente` varchar(30) DEFAULT NULL,
  `tipo_patente` varchar(10) DEFAULT NULL,
  `stato` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `autista`
--

INSERT INTO `autista` (`id`, `nome`, `cognome`, `telefono`, `email`, `patente`, `tipo_patente`, `stato`) VALUES
(4, 'fernando', 'marra', '112233', 'ffffff@gmail.com', 'ccccc55555', 'a', 1),
(5, 'tonio', 'danna', '111', 'xxxx@gmail.com', 'xxxwwww', 'd', 1),
(6, 'gianni', 'danna', '4444', 'gggg@gmail.com', 'ee55qq', 'd', 1),
(7, 'martina', 'potuto', '3388798993', 'martystar1000@gmail.com', '', '', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `automezzo`
--

CREATE TABLE `automezzo` (
  `id` int(11) NOT NULL,
  `modello` varchar(100) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `targa` varchar(20) NOT NULL,
  `anno` year(4) NOT NULL,
  `colore` varchar(50) NOT NULL,
  `stato` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `automezzo`
--

INSERT INTO `automezzo` (`id`, `modello`, `marca`, `targa`, `anno`, `colore`, `stato`) VALUES
(1, 'classe a', 'mercedes', 'ww33ee', '2000', 'bianco', 1),
(2, 'classe c', 'mercedes', 'ww33eex', '2020', 'nero', 1),
(3, 'pulmino', 'mercedes', 'eee444555666', '2000', 'bianco', 1),
(4, 'cla', 'mercedes', 'aa333bb', '2022', 'rosso', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `codice_fiscale` varchar(20) DEFAULT NULL,
  `partita_iva` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `indirizzo` varchar(255) DEFAULT NULL,
  `stato` tinyint(1) NOT NULL DEFAULT 1,
  `privacy_consenso` tinyint(1) NOT NULL DEFAULT 0,
  `privacy_marketing` tinyint(1) NOT NULL DEFAULT 0,
  `privacy_terzi` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `cliente`
--

INSERT INTO `cliente` (`id`, `nome`, `cognome`, `codice_fiscale`, `partita_iva`, `telefono`, `email`, `indirizzo`, `stato`, `privacy_consenso`, `privacy_marketing`, `privacy_terzi`, `created_at`, `password`, `reset_token`, `reset_expires`) VALUES
(1, 'martina', 'potuto', '', '', '3388798993', 'martystar1000@gmail.com', 'via campaldino 11', 1, 0, 0, 0, '2025-09-30 13:47:24', '$2y$10$PvNNPTSPpjWI6d/bGRav3ez5SzgQ4gs/y4w9BdbaVK0paB3wvT4J2', NULL, NULL),
(2, 'matteo', 'potuto', '', '', '347795560', 'matteoweb2005@gmail.com', 'Via Domenico Modugno 10', 1, 0, 0, 0, '2025-09-30 13:47:24', NULL, NULL, NULL),
(3, 'Vincenzo', 'Potuto', NULL, NULL, '3388798993', 'mattemartivince@gmail.com', 'Domenico Modugno 10', 1, 0, 1, 0, '2025-09-30 13:47:24', '$2y$10$aTlkcbnHD05eAhxcE58hCOhn1G5LL6Hyjd9bv8dSaORzMYNY.bF/S', NULL, NULL),
(4, 'cosimo', 'potuto', NULL, NULL, '3388798993', 'vpotuto@libero.it', 'Via Domenico Modugno 10', 1, 0, 0, 0, '2025-09-30 13:47:24', '$2y$10$lDaWS5gGaNLjkb/XgLLj1.V2eRMZhuI728IadhYZIYwpgzfHdGJEO', NULL, NULL),
(5, 'simone', 'potuto', NULL, NULL, '3388798993', 'skyenzovp@gmail.com', 'via campaldino 11', 1, 1, 0, 0, '2025-09-30 13:47:46', '$2y$10$PYiQUu0wcLq68Xp1AtKU6.1c5P3d84ZwDuoLcswITgLpx2UWW8mjy', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `fattura`
--

CREATE TABLE `fattura` (
  `id` int(11) NOT NULL,
  `noleggio_id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `importo` decimal(10,2) NOT NULL,
  `iva` decimal(5,2) DEFAULT NULL,
  `iva_calcolata` decimal(10,2) DEFAULT NULL,
  `totale_con_iva` decimal(10,2) DEFAULT NULL,
  `descrizione` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `fattura`
--

INSERT INTO `fattura` (`id`, `noleggio_id`, `numero`, `data`, `importo`, `iva`, `iva_calcolata`, `totale_con_iva`, `descrizione`) VALUES
(1, 1, '111', '2025-09-29', 1000.00, 22.00, 220.00, 1220.00, ''),
(2, 7, '1112', '2025-10-01', 430000.00, 22.00, 94600.00, 524600.00, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `fornitore`
--

CREATE TABLE `fornitore` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `indirizzo` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `codice_fiscale` varchar(20) NOT NULL,
  `partita_iva` varchar(20) NOT NULL,
  `stato` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `fornitore`
--

INSERT INTO `fornitore` (`id`, `nome`, `cognome`, `telefono`, `email`, `indirizzo`, `note`, `codice_fiscale`, `partita_iva`, `stato`) VALUES
(1, 'aaa', 'sss', '111', 'skyenzovp@gmail.com', '', '', '', '', 1),
(2, 'fer', 'cc', '3388798993', 'mmm@gmail.com', 'xxx', '', '', '', 1),
(3, 'Vincenzo', 'Potuto', '000000', '', 'Domenico Modugno 10', '', '', '', 1),
(4, 'martina', 'potuto', '3388798993', 'martystar1000@gmail.com', 'via campaldino 11', NULL, '', '', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `noleggio`
--

CREATE TABLE `noleggio` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `tipologia_noleggio_id` int(11) NOT NULL,
  `automezzo_id` int(11) DEFAULT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `importo` decimal(10,2) DEFAULT NULL,
  `destinazione` varchar(255) DEFAULT NULL,
  `accompagnatore` varchar(100) DEFAULT NULL,
  `preventivo` tinyint(1) NOT NULL DEFAULT 0,
  `pagato` tinyint(1) NOT NULL DEFAULT 0,
  `ivato` tinyint(1) NOT NULL DEFAULT 0,
  `autista1_id` int(11) DEFAULT NULL,
  `autista2_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `noleggio`
--

INSERT INTO `noleggio` (`id`, `cliente_id`, `tipologia_noleggio_id`, `automezzo_id`, `data_inizio`, `data_fine`, `importo`, `destinazione`, `accompagnatore`, `preventivo`, `pagato`, `ivato`, `autista1_id`, `autista2_id`) VALUES
(1, 5, 4, 1, '2025-10-01', '2025-10-31', NULL, 'bologna', NULL, 0, 1, 0, NULL, NULL),
(2, 2, 1, 2, '2025-09-29', '2025-10-02', NULL, 'Milano', NULL, 0, 0, 0, 4, NULL),
(5, 3, 4, 3, '2025-10-06', '2025-10-09', NULL, 'milano', NULL, 0, 0, 0, NULL, NULL),
(7, 4, 3, 4, '2025-10-13', '2025-10-19', NULL, 'bologna', NULL, 0, 0, 0, NULL, NULL),
(8, 5, 1, NULL, '2025-10-06', '2025-10-09', NULL, 'Brindisi', 'manuela', 0, 0, 0, NULL, NULL),
(18, 5, 4, NULL, '2025-10-05', '2025-10-06', NULL, 'campagna', NULL, 0, 0, 0, NULL, NULL),
(19, 5, 1, NULL, '2025-10-30', '2025-11-05', NULL, 'campagna', NULL, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `tipologie_noleggio`
--

CREATE TABLE `tipologie_noleggio` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tipologie_noleggio`
--

INSERT INTO `tipologie_noleggio` (`id`, `nome`) VALUES
(4, 'autobus 20 posti'),
(3, 'autobus 50 posti'),
(5, 'autocarro'),
(2, 'noleggi con conducente'),
(1, 'noleggio senza conducente');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$loQtNUskWCD6a4bl/yWyqOpTULRxv499SX3oAwKnYXZUGwierjjz6');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `autista`
--
ALTER TABLE `autista`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `automezzo`
--
ALTER TABLE `automezzo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `targa` (`targa`);

--
-- Indici per le tabelle `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `fattura`
--
ALTER TABLE `fattura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noleggio_id` (`noleggio_id`);

--
-- Indici per le tabelle `fornitore`
--
ALTER TABLE `fornitore`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `noleggio`
--
ALTER TABLE `noleggio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `tipologia_noleggio_id` (`tipologia_noleggio_id`),
  ADD KEY `automezzo_id` (`automezzo_id`),
  ADD KEY `autista1_id` (`autista1_id`),
  ADD KEY `autista2_id` (`autista2_id`);

--
-- Indici per le tabelle `tipologie_noleggio`
--
ALTER TABLE `tipologie_noleggio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `autista`
--
ALTER TABLE `autista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `automezzo`
--
ALTER TABLE `automezzo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `fattura`
--
ALTER TABLE `fattura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `fornitore`
--
ALTER TABLE `fornitore`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `noleggio`
--
ALTER TABLE `noleggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT per la tabella `tipologie_noleggio`
--
ALTER TABLE `tipologie_noleggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `fattura`
--
ALTER TABLE `fattura`
  ADD CONSTRAINT `fattura_ibfk_1` FOREIGN KEY (`noleggio_id`) REFERENCES `noleggio` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `noleggio`
--
ALTER TABLE `noleggio`
  ADD CONSTRAINT `noleggio_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `noleggio_ibfk_2` FOREIGN KEY (`tipologia_noleggio_id`) REFERENCES `tipologie_noleggio` (`id`),
  ADD CONSTRAINT `noleggio_ibfk_3` FOREIGN KEY (`automezzo_id`) REFERENCES `automezzo` (`id`),
  ADD CONSTRAINT `noleggio_ibfk_4` FOREIGN KEY (`autista1_id`) REFERENCES `autista` (`id`),
  ADD CONSTRAINT `noleggio_ibfk_5` FOREIGN KEY (`autista2_id`) REFERENCES `autista` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
