<?php

//error_reporting(E_ALL);

define("TRUSTED_DEBUG", false);

/* ===== Database ===== */
//Использовать базу данных
define("TRUSTED_DB", true);
//Хост базы данных
define("TRUSTED_DB_HOST", "localhost");
//Имя базы данных
define("TRUSTED_DB_NAME", "trusted_test");
//Логин для доступа к базе данных
define("TRUSTED_DB_LOGIN", "root");
//Пароль для доступа к базе данных
define("TRUSTED_DB_PASSWORD", "root");


/* ===== Module trustednet ===== */
//Путь к модулю trustednet
define('TRUSTED_MODULE_PATH', '/trusted');


/* ===== Login ===== */
//Учетные данные приложения trusted.login
define("TRUSTED_LOGIN_CLIENT_ID", "CLIENT ID VALUE");
define("TRUSTED_LOGIN_CLIENT_SECRET", "SECRET VALUE");
//Путь перехода после успешной аутентификации
define("TRUSTED_AUTHORIZED_REDIRECT", "/demo/index.php");
