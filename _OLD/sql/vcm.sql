-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Lug 24, 2024 alle 22:37
-- Versione del server: 10.4.25-MariaDB
-- Versione PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vcm`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `competizioni`
--

CREATE TABLE `competizioni` (
  `utente` varchar(200) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `modalita` varchar(200) NOT NULL,
  `gironi` int(50) NOT NULL,
  `ar` int(50) NOT NULL,
  `partecipanti` int(50) NOT NULL,
  `fasefinale` int(11) NOT NULL,
  `finita` int(50) NOT NULL,
  `squadre` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi`
--

CREATE TABLE `gruppi` (
  `utente` varchar(200) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `colore1` varchar(200) NOT NULL,
  `colore2` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `gruppi`
--

INSERT INTO `gruppi` (`utente`, `nome`, `colore1`, `colore2`) VALUES
('a', '0', '#000000', '#ffffff'),
('a', 'Serie A', '#0000ff', '#ffffff'),
('a', 'Serie B', '#008000', '#ffffff');

-- --------------------------------------------------------

--
-- Struttura della tabella `squadre`
--

CREATE TABLE `squadre` (
  `utente` varchar(200) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `forza` int(50) NOT NULL,
  `gruppo` varchar(200) NOT NULL DEFAULT '0',
  `colore1` varchar(200) NOT NULL,
  `colore2` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `squadre`
--

INSERT INTO `squadre` (`utente`, `nome`, `forza`, `gruppo`, `colore1`, `colore2`) VALUES
('a', 'Atalanta', 436, 'Serie A', '#0080ff', '#000000'),
('a', 'Bologna', 277, 'Serie A', '#ff0000', '#0000a0'),
('a', 'Brescia', 19, 'Serie B', '#ffffff', '#0000ff'),
('a', 'Cagliari', 71, 'Serie A', '#ff2020', '#0000a0'),
('a', 'Cesena', 13, 'Serie B', '#ffffff', '#000000'),
('a', 'Como', 61, 'Serie A', '#ffffff', '#0000a0'),
('a', 'Cosenza', 13, 'Serie B', '#ff0000', '#000040'),
('a', 'Cremonese', 37, 'Serie B', '#df0000', '#cda432'),
('a', 'Empoli', 39, 'Serie A', '#0000ff', '#ffffff'),
('a', 'Fiorentina', 234, 'Serie A', '#8000ff', '#ff0080'),
('a', 'Frosinone', 28, 'Serie B', '#ffff00', '#0000ff'),
('a', 'Genoa', 987, 'Serie A', '#ff0000', '#0000ff'),
('a', 'Inter', 713, 'Serie A', '#0080ff', '#000000'),
('a', 'Juventus', 566, 'Serie A', '#000000', '0'),
('a', 'Lazio', 228, 'Serie A', '#ffffff', '#80ffff'),
('a', 'Lecce', 96, 'Serie A', '#408080', '#ffff80'),
('a', 'Milan', 565, 'Serie A', '#ff0000', '#000000'),
('a', 'Modena', 18, 'Serie B', '#ffff80', '#0000a0'),
('a', 'Monza', 95, 'Serie A', '#ff0000', '#ffffff'),
('a', 'Napoli', 495, 'Serie A', '#ffffff', '#80ffff'),
('a', 'Palermo', 40, 'Serie B', '#ff80ff', '#000000'),
('a', 'Parma', 86, 'Serie A', '#cda434', '#000000'),
('a', 'Pisa', 28, 'Serie B', '#1414ff', '#ffffff'),
('a', 'Roma', 276, 'Serie A', '#ffb000', '#ff0000'),
('a', 'Salernitana', 57, 'Serie B', '#800000', '#ffffff'),
('a', 'Sampdoria', 33, 'Serie B', '#0000ff', '#ffffff'),
('a', 'Sassuolo', 120, 'Serie B', '#008000', '#000000'),
('a', 'Spezia', 26, 'Serie B', '#ffffff', '#000000'),
('a', 'Torino', 157, 'Serie A', '#800000', '#ffffff'),
('a', 'Udinese', 149, 'Serie A', '#808080', '#ffffff'),
('a', 'Venezia', 47, 'Serie A', '#008000', '#ff8040'),
('a', 'Verona', 62, 'Serie A', '#0000a0', '#ffff00');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`username`, `password`) VALUES
('a', '$2y$10$.Gie7sOEKUy6N9YQu39ANOMKJ9RjOKMHILymY3oYaxNiVkY3k6pPK');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `competizioni`
--
ALTER TABLE `competizioni`
  ADD PRIMARY KEY (`utente`,`nome`);

--
-- Indici per le tabelle `gruppi`
--
ALTER TABLE `gruppi`
  ADD PRIMARY KEY (`utente`,`nome`);

--
-- Indici per le tabelle `squadre`
--
ALTER TABLE `squadre`
  ADD PRIMARY KEY (`utente`,`nome`,`gruppo`),
  ADD KEY `squadre_ibfk_1` (`utente`,`gruppo`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `competizioni`
--
ALTER TABLE `competizioni`
  ADD CONSTRAINT `competizioni_ibfk_1` FOREIGN KEY (`utente`) REFERENCES `squadre` (`utente`);

--
-- Limiti per la tabella `gruppi`
--
ALTER TABLE `gruppi`
  ADD CONSTRAINT `gruppi_ibfk_1` FOREIGN KEY (`utente`) REFERENCES `users` (`username`);

--
-- Limiti per la tabella `squadre`
--
ALTER TABLE `squadre`
  ADD CONSTRAINT `squadre_ibfk_1` FOREIGN KEY (`utente`,`gruppo`) REFERENCES `gruppi` (`utente`, `nome`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
