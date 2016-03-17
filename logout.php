<?php
/* Подключаем модуль Trusted.Login */

require_once './trusted/config.php'; //указать путь до настроек модуля
require_once TRUSTED_MODULE_AUTH;  //подключить сам модуль Trusted.Login

OAuth2::remove();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="css/tlogin-2.0.1.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="page">
        <h1>Вы успешно вышли из системы</h1>
        <div class='view-contaier'>
            <a href="index.php" class='view-login'>На главную</a>
        </div>
    </div>
</body>
</html>
