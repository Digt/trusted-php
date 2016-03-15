<?php

// error_reporting(E_ALL);

define("TRUSTED_DEBUG", false);

/* ===== Database ===== */
// Использовать базу данных
define("TRUSTED_DB", false);
// Хост базы данных
define("TRUSTED_DB_HOST", "localhost");
// Имя базы данных
define("TRUSTED_DB_NAME", "trusted_test");
// Логин для доступа к базе данных
define("TRUSTED_DB_LOGIN", "root");
// Пароль для доступа к базе данных
define("TRUSTED_DB_PASSWORD", "root");


/* ===== Module trustednet ===== */
// Путь к модулю trustednet
define('TRUSTED_MODULE_PATH', '/trusted');


/* ===== Login ===== */
// Учетные данные приложения trusted.login
define("TRUSTED_LOGIN_CLIENT_ID", "995134bae1b5e45a447502778007f9f6");
define("TRUSTED_LOGIN_CLIENT_SECRET", "secret");
// Путь перехода после успешной аутентификации
define("TRUSTED_AUTHORIZED_REDIRECT", "index.php");
