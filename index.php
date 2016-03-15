<?php
/* Подключаем модуль Trusted.Login */

require_once './trusted/config.php'; //указать путь до настроек модуля
require_once TRUSTED_MODULE_AUTH;  //подключить сам модуль Trusted.Login
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    </head>
    <body>

        <h1>Тестовая страница Trusted.Login </h1>

        <?php
        $token = OAuth2::getFromSession(); //Получаем токен
        if ($token) {
            $user = $token->getUser();
            $suser = $user->getServiceUser();
            echo "<div>";
            echo "<img src='".$suser->getAvatarUrl($token->getAccessToken())."'/>";
            echo "<span class='user-name'>" . $suser->getDisplayName() . "</span>";
            echo "<a href='#'>Выход</a>";
            echo "</div>";
        } else {
            // Вставка виджета Trusted.Login
            include './tlogin.tpl';
        }
        ?>        
    </body>
</html>

