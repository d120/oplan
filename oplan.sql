-- MySQL dump 10.13  Distrib 5.5.55, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: oplan
-- ------------------------------------------------------
-- Server version	5.5.55-0+deb8u1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `raum`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `raum` (
  `nummer` varchar(20) NOT NULL DEFAULT '',
  `platz` int(11) NOT NULL,
  `verw_mit` varchar(10) NOT NULL DEFAULT '',
  `verw_id` varchar(25) NOT NULL DEFAULT '',
  `verw_link` text NOT NULL,
  `verw_kommentar` text NOT NULL,
  `raumtyp` text NOT NULL,
  `beamer` int(11) DEFAULT NULL,
  PRIMARY KEY (`nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raumbedarf`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `raumbedarf` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `termin_id` int(11) NOT NULL,
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `min_platz` int(11) NOT NULL,
  `kommentar` text NOT NULL,
  `praeferenz` varchar(20) NOT NULL DEFAULT '',
  `zielgruppe` varchar(40) NOT NULL DEFAULT '',
  `raum` varchar(20) NOT NULL DEFAULT '',
  `exp_farbe` varchar(6) NOT NULL DEFAULT '',
  `exp_raum` varchar(50) NOT NULL DEFAULT '',
  `exp_kommentar` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2652 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `raumfrei`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `raumfrei` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `raum_nummer` varchar(20) NOT NULL DEFAULT '',
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `status` text NOT NULL,
  `kommentar` text NOT NULL,
  `platz` int(11) NOT NULL,
  `blocked` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5204 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `termin`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `termin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `raum_nummer` varchar(20) NOT NULL DEFAULT '',
  `zielgruppe` varchar(40) NOT NULL DEFAULT '',
  `farbe` varchar(10) NOT NULL DEFAULT '',
  `kommentar` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) DEFAULT NULL,
  `pwhash` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-14  1:27:18
