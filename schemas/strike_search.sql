-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2016 at 10:05 AM
-- Server version: 10.1.8-MariaDB
-- PHP Version: 5.6.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `strike_search`
--
CREATE DATABASE IF NOT EXISTS `strike_search` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `strike_search`;

-- --------------------------------------------------------

--
-- Table structure for table `descriptions`
--

CREATE TABLE IF NOT EXISTS `descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_hash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torrent_hash` (`torrent_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1580333 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_info`
--

CREATE TABLE IF NOT EXISTS `file_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_hash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `file_names` text COLLATE utf8_unicode_ci,
  `file_sizes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torrent_hash` (`torrent_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2186407 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdbids`
--

CREATE TABLE IF NOT EXISTS `imdbids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_hash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `imdb_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrent_hash` (`torrent_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=248672 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdb_episodes`
--

CREATE TABLE IF NOT EXISTS `imdb_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imdbId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Year` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Rating` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Runtime` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Genre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Released` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Season` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Episode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `SeriesID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Director` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Writer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Cast` text COLLATE utf8_unicode_ci NOT NULL,
  `Metacritic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `imdbRating` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `imdbVotes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Poster` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Plot` text COLLATE utf8_unicode_ci NOT NULL,
  `FullPlot` text COLLATE utf8_unicode_ci NOT NULL,
  `Langauge` text COLLATE utf8_unicode_ci NOT NULL,
  `Country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Awards` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastUpdated` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imdbId` (`imdbId`),
  KEY `Title` (`Title`),
  KEY `Year` (`Year`),
  KEY `Episode` (`Episode`),
  KEY `Season` (`Season`),
  KEY `SeriesID` (`SeriesID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4769489 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdb_info`
--

CREATE TABLE IF NOT EXISTS `imdb_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imdbID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Year` int(255) NOT NULL,
  `Rating` int(255) NOT NULL,
  `Runtime` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Genre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Released` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Director` text COLLATE utf8_unicode_ci NOT NULL,
  `Writer` text COLLATE utf8_unicode_ci NOT NULL,
  `Cast` text COLLATE utf8_unicode_ci NOT NULL,
  `Metacritic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `imdbRating` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `imdbVotes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Poster` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Plot` text COLLATE utf8_unicode_ci NOT NULL,
  `FullPlot` text COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Awards` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastUpdated` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imdbID` (`imdbID`),
  KEY `Title` (`Title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4771595 ;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE IF NOT EXISTS `terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phrase` (`phrase`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1597410 ;

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_hash` varchar(40) NOT NULL,
  `torrent_title` tinytext NOT NULL,
  `torrent_category` varchar(50) NOT NULL,
  `sub_category` varchar(50) NOT NULL,
  `torrent_page` varchar(255) NOT NULL,
  `seeds` int(11) NOT NULL,
  `leeches` int(11) NOT NULL,
  `file_count` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `upload_date` varchar(35) NOT NULL,
  `uploader_username` varchar(50) NOT NULL,
  `verified` tinyint(4) NOT NULL,
  `imdbid` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torrent_hash` (`torrent_hash`),
  KEY `seeds` (`seeds`),
  KEY `sub_category` (`sub_category`),
  KEY `torrent_category` (`torrent_category`),
  FULLTEXT KEY `torrent_title` (`torrent_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC TRANSACTIONAL=0 AUTO_INCREMENT=7019530 ;

-- --------------------------------------------------------

--
-- Table structure for table `tracked_terms`
--

CREATE TABLE IF NOT EXISTS `tracked_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `time_stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1604194 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
