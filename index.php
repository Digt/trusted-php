<?
/*Подключаем модуль Trusted.Login*/

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
    
    <?
    //Определяем , пользователь авторизовани или нет
    //При успешной авторизации, в сессию добавляется OAuth токен
    $token = OAuth2::getFromSession();//Получаем токен

    if (!$token) {//Если пользователь не авторизован, показать виджет
    ?>
    
    <!-- Вставка виджета Trusted.Login-->
    <script src='https://net.trusted.ru/static/js/tlogin.js'></script>
    <div id='trusted-login' data-login='{"display":"big","providers":["trustednet","vk","fbook","google","odnoklassniki","mailru","twitter"],"hidden":"none","form":"rounded","redirect_uri":"<?= TRUSTED_AUTH_REDIRECT_URI ?>","client_id":"<?= TRUSTED_LOGIN_CLIENT_ID ?>"}'></div>
    
    <?}
    else {//Если пользователь авторизован, вывести информацию по нему
      $user = $token->getUser();//Получить пользователя из OAuth сессии        
      ?>
      <div>
          <h3> Пользователь</h3>
          <pre><?print_r ($user->getServiceUser()); //Получить данные о пользователе?></pre>
      </div>
    <?
    }
    ?>
    
    <? OAuth2::remove(); // При обновлении страницы завершить сессию?>
 
    </body>
</html>
