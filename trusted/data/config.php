<?php

require_once (__DIR__ . '/../config.php');

//data base
define("TRUSTEDNET_DB_TABLE_USER", "trn_user");

//path
define('TRUSTED_MODULE_DATA_PATH', '/data');
define('TRUSTED_MODULE_DATA_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_DATA_PATH);
define('TRUSTED_MODULE_DATA', TRUSTED_MODULE_DATA_ROOT . '/common.php');

//Module URI
define('TRUSTED_URI_MODULE_DATA', TRUSTED_URI_MODULE . TRUSTED_MODULE_DATA_PATH);
