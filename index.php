<?php
/* Подключаем модуль Trusted.Login */

require_once './trusted/config.php'; //указать путь до настроек модуля
require_once TRUSTED_MODULE_AUTH;  //подключить сам модуль Trusted.Login

if (true){
    define("TRUSTED_LOGIN_PLUGIN_PATH", "https://net.trusted.ru/static/");
}
else{
    define("TRUSTED_LOGIN_PLUGIN_PATH", "");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
        <link rel="stylesheet" href="<?=TRUSTED_LOGIN_PLUGIN_PATH?>css/tlogin-2.0.1.css">
        <link rel="stylesheet" href="css/main.css">
    </head>
    <body>

        <div class="page">
            <h1>Тестовая страница Trusted.Login </h1>

            <?php
                // session_unset();
                $token = OAuth2::getFromSession(); //Получаем токен
                if ($token) {
                    $user = $token->getUser();
                    $suser = $user->getServiceUser();
                    echo "<div class='view-contaier'>";
                    echo "<div class='profile'>";
                    echo "<div style='width: 50px; height: 50px; border-radius: 100%; background: url(".$suser->getAvatarUrl($token->getAccessToken()).") no-repeat; background-size: contain; display: inline-block'></div>";
                    echo "<span class='user-name'>" . $suser->getDisplayName() . "</span>";
                    echo "<a class='view-login' href='logout.php'>Выход</a>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    // Вставка виджета Trusted.Login
                    include './tlogin.tpl';
                }
            ?>
        </div>        
    </body>
</html>

