-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Nov 11, 2019 alle 21:54
-- Versione del server: 10.4.8-MariaDB
-- Versione PHP: 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ship_tracker`
--

DELIMITER $$
--
-- Funzioni
--
CREATE DEFINER=`root`@`localhost` FUNCTION `haversine` (`lat1` DECIMAL(8,6), `lng1` DECIMAL(8,6), `lat2` DECIMAL(8,6), `lng2` DECIMAL(8,6)) RETURNS DECIMAL(10,0) NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
DECLARE R INT;
    DECLARE dLat DECIMAL(30,15);
    DECLARE dLng DECIMAL(30,15);
    DECLARE a1 DECIMAL(30,15);
    DECLARE a2 DECIMAL(30,15);
    DECLARE a DECIMAL(30,15);
    DECLARE c DECIMAL(30,15);
    DECLARE d DECIMAL(30,15);

    SET R = 6371; 
    SET dLat = RADIANS( lat2 ) - RADIANS( lat1 );
    SET dLng = RADIANS( lng2 ) - RADIANS( lng1 );
    SET a1 = SIN( dLat / 2 ) * SIN( dLat / 2 );
    SET a2 = SIN( dLng / 2 ) * SIN( dLng / 2 ) * COS( RADIANS( lng1 )) * COS( RADIANS( lat2 ) );
    SET a = a1 + a2;
    SET c = 2 * ATAN2( SQRT( a ), SQRT( 1 - a ) );
    SET d = R * c;
RETURN d;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `boas`
--

CREATE TABLE `boas` (
  `Id` int(11) NOT NULL,
  `Latitude` double NOT NULL,
  `Longitude` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `boas`
--

INSERT INTO `boas` (`Id`, `Latitude`, `Longitude`) VALUES
(1, 42.367422, 13.3492),
(2, 42.067422, 13.3492),
(3, 42.367422, 13.0492),
(4, 42.067422, 13.0492);

-- --------------------------------------------------------

--
-- Struttura della tabella `positions`
--

CREATE TABLE `positions` (
  `Id` int(11) NOT NULL,
  `Type` int(11) NOT NULL,
  `IdRef` int(11) NOT NULL,
  `Latitude` double NOT NULL,
  `Longitude` double NOT NULL,
  `Time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `positions`
--

INSERT INTO `positions` (`Id`, `Type`, `IdRef`, `Latitude`, `Longitude`, `Time`) VALUES
(1, 1, 1, 42.367422, 13.3492, '2018-02-22 00:26:12'),
(2, 1, 1, 42.067422, 13.3492, '2018-02-22 00:26:17');

-- --------------------------------------------------------

--
-- Struttura della tabella `races`
--

CREATE TABLE `races` (
  `Id` int(11) NOT NULL,
  `Track` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `races`
--

INSERT INTO `races` (`Id`, `Track`) VALUES
(1, 8),
(13, 8),
(14, 8),
(15, 8);

-- --------------------------------------------------------

--
-- Struttura della tabella `traces`
--

CREATE TABLE `traces` (
  `Id` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `User` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `traces`
--

INSERT INTO `traces` (`Id`, `Name`, `User`) VALUES
(1, 'Test 1', 1),
(2, 'Test 2', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `tracks`
--

CREATE TABLE `tracks` (
  `Id` int(11) NOT NULL,
  `User` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dump dei dati per la tabella `tracks`
--

INSERT INTO `tracks` (`Id`, `User`, `Name`) VALUES
(8, 1, 'Test1'),
(9, 1, 'Test2'),
(10, 1, 'Test3');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`Id`, `Username`, `Password`) VALUES
(1, 'admin', '0');

-- --------------------------------------------------------

--
-- Struttura della tabella `waypoints`
--

CREATE TABLE `waypoints` (
  `Id` int(11) NOT NULL,
  `Track` int(11) NOT NULL,
  `Boa` int(11) NOT NULL,
  `Number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dump dei dati per la tabella `waypoints`
--

INSERT INTO `waypoints` (`Id`, `Track`, `Boa`, `Number`) VALUES
(22, 8, 1, 1),
(23, 8, 2, 2),
(26, 9, 4, 1),
(27, 9, 1, 2),
(28, 9, 3, 3),
(29, 9, 2, 4),
(30, 10, 2, 1),
(31, 10, 1, 2),
(32, 10, 3, 3);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `boas`
--
ALTER TABLE `boas`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `races`
--
ALTER TABLE `races`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Track` (`Track`);

--
-- Indici per le tabelle `traces`
--
ALTER TABLE `traces`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `User` (`User`);

--
-- Indici per le tabelle `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `User` (`User`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `waypoints`
--
ALTER TABLE `waypoints`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Track` (`Track`),
  ADD KEY `Boa` (`Boa`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `boas`
--
ALTER TABLE `boas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `positions`
--
ALTER TABLE `positions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `races`
--
ALTER TABLE `races`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `traces`
--
ALTER TABLE `traces`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `tracks`
--
ALTER TABLE `tracks`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `waypoints`
--
ALTER TABLE `waypoints`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `races`
--
ALTER TABLE `races`
  ADD CONSTRAINT `races_ibfk_1` FOREIGN KEY (`Track`) REFERENCES `tracks` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `traces`
--
ALTER TABLE `traces`
  ADD CONSTRAINT `traces_ibfk_1` FOREIGN KEY (`User`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `tracks`
--
ALTER TABLE `tracks`
  ADD CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`User`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `waypoints`
--
ALTER TABLE `waypoints`
  ADD CONSTRAINT `waypoints_ibfk_1` FOREIGN KEY (`Track`) REFERENCES `tracks` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `waypoints_ibfk_2` FOREIGN KEY (`Boa`) REFERENCES `boas` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
