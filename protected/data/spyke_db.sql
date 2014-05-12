-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Май 12 2014 г., 20:35
-- Версия сервера: 5.5.32
-- Версия PHP: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `spyke_db`
--
CREATE DATABASE IF NOT EXISTS `spyke_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `spyke_db`;

-- --------------------------------------------------------

--
-- Структура таблицы `spk_link`
--

CREATE TABLE IF NOT EXISTS `spk_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `spk_link_word`
--

CREATE TABLE IF NOT EXISTS `spk_link_word` (
  `wordId` int(11) NOT NULL,
  `linkId` int(11) NOT NULL,
  KEY `wordId` (`wordId`,`linkId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `spk_url_list`
--

CREATE TABLE IF NOT EXISTS `spk_url_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `spk_word_list`
--

CREATE TABLE IF NOT EXISTS `spk_word_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `spk_word_location`
--

CREATE TABLE IF NOT EXISTS `spk_word_location` (
  `urlId` int(11) NOT NULL,
  `wordId` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  KEY `urlId` (`urlId`,`wordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
