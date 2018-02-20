-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Feb 16, 2018 alle 11:24
-- Versione del server: 5.7.19-0ubuntu0.16.04.1
-- Versione PHP: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `password`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `password`
--

CREATE TABLE `password` (
  `id` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `encPassword` blob NOT NULL,
  `url` varchar(512) NOT NULL,
  `note` text NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editDate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `share`
--

CREATE TABLE `share` (
  `id` int(11) NOT NULL,
  `passwordId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `encPassword` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `user_login`
--

CREATE TABLE `user_login` (
  `id` int(20) NOT NULL,
  `password` varchar(256) NOT NULL,
  `full_name` varchar(20) NOT NULL,
  `privkey` text NOT NULL,
  `pubkey` text NOT NULL,
  `email` varchar(250) NOT NULL,
  `notifyOnShare` tinyint(1) NOT NULL DEFAULT '1',
  `notifyOnUpdate` tinyint(1) NOT NULL DEFAULT '1',
  `isActive` tinyint(1) NOT NULL DEFAULT '1',
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `v_sharedWith`
--
CREATE TABLE `v_sharedWith` (
`id` int(20)
,`url` varchar(512)
,`username` varchar(256)
,`email` varchar(250)
,`idSharedUser` int(11)
);

-- --------------------------------------------------------

--
-- Struttura per la vista `v_sharedWith`
--
DROP TABLE IF EXISTS `v_sharedWith`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sharedWith`  AS  select `user_login`.`id` AS `id`,`url` AS `url`,`username` AS `username`,`user_login`.`email` AS `email`,`share`.`userId` AS `idSharedUser` from ((`share` left join `password` on((`share`.`passwordId` = `id`))) left join `user_login` on((`ownerId` = `user_login`.`id`))) ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `password`
--
ALTER TABLE `password`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `share`
--
ALTER TABLE `share`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `password`
--
ALTER TABLE `password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `share`
--
ALTER TABLE `share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `user_login`
--
ALTER TABLE `user_login`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
