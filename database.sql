-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Feb 21, 2018 alle 14:00
-- Versione del server: 5.7.19-0ubuntu0.16.04.1
-- Versione PHP: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `password`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `activation`
--

CREATE TABLE `activation` (
  `userId` int(11) NOT NULL,
  `guid` varchar(256) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `isActive` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Struttura per la vista `v_sharedWith`
--
DROP VIEW IF EXISTS `v_sharedWith`;

create view v_sharedWith as select 
        `user_login`.`id` AS `id`,
        `user_login`.`full_name` AS `full_name`,
        `user_login`.`email` AS `email`,
        `password`.`url` AS `url`,
        `password`.`username` AS `username`,
        `password`.`note` AS `passwordnote`,
        `share`.`userId` AS `idSharedUser`
        from `share` 
        left join `password` on `share`.`passwordId` = `password`.`id`
        left join `user_login` on((`ownerId` = `user_login`.`id`));

-- --------------------------------------------------------

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
