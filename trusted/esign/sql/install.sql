CREATE TABLE IF NOT EXISTS `trn_documents` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ORIGINAL_NAME` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SYS_NAME` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DESCRIPTION` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PATH` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SIGNERS` text COLLATE utf8_unicode_ci,
  `PARENT_ID` int(11) DEFAULT NULL,
  `TYPE` tinyint(1) DEFAULT '0',
  `CHILD_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_trn_documents_trn_documents1_idx` (`PARENT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `trn_documents_property` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TYPE` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VALUE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `fk_trn_documents_property_trn_documents_idx` (`PARENT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `trn_documents_status` (
  `DOCUMENT_ID` int(11) NOT NULL,
  `STATUS` tinyint(4) DEFAULT '0',
  `CREATED` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `dicument_id_UNIQUE` (`DOCUMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;