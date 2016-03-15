<?php

require_once (__DIR__ . '/config.php');
require_once TRUSTED_MODULE_ROOT . '/data/config.php';
require_once TRUSTED_MODULE_AUTH_ROOT . '/oauth2.php';

if (TRUSTED_DB) {
    $DB->Query('
        CREATE TABLE IF NOT EXISTS `trn_user` (
            `ID` int(11) NOT NULL,
            `USER_ID` int(18) DEFAULT NULL,
            `TIMESTAMP_X` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
}