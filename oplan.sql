/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raum` (
  `nummer` varchar(20) NOT NULL DEFAULT '',
  `platz` int(11) NOT NULL,
  `verw_mit` varchar(10) NOT NULL,
  `verw_id` varchar(25) NOT NULL,
  `verw_link` text NOT NULL,
  `verw_kommentar` text NOT NULL,
  `raumtyp` text NOT NULL,
  PRIMARY KEY (`nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raumbedarf` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `min_platz` int(11) NOT NULL,
  `kommentar` text NOT NULL,
  `praeferenz` varchar(20) NOT NULL DEFAULT '',
  `zielgruppe` varchar(40) NOT NULL DEFAULT '',
  `raum` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2213 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raumfrei` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `raum_nummer` varchar(20) NOT NULL DEFAULT '',
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `status` text NOT NULL,
  `kommentar` text NOT NULL,
  `platz` int(11) NOT NULL,
  `blocked` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=619 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raumnutzung` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bedarf_id` int(11) NOT NULL,
  `raum_nummer` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) DEFAULT NULL,
  `pwhash` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
