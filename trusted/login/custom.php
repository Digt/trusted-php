<?php

/**
 * Событие происходит если пользователь уже зарегистрирован в системе
 * @param \TUser $user Пользователе
 */
function onRegUserFound($user) {
    //Ваш код здесь
}

/**
 * Событие происходит до момента добавления информации о привязке пользователя в БД
 * @param \TUser $user Пользователь
 * @throws OAuth2Exception
 */
function onBeforeUserInsert(&$user) {
    //Ваш код здесь
}

/**
 * Событие происходит после успешной авторизации пользователя
 * @param \TUser $user
 */
function onUserAuthorized($user) {
    //Ваш код здесь
}

/**
 * Событие на возникновение ошибки OAuth2Exception
 * @param \OAuth2Exception $e
 */
function onOAuth2Exception($e) {
    //Ваш код здесь
    onException($e);
}

/**
 * Событие на возникновение ошибки Exception
 * @param \Exception $e
 */
function onException($e) {
    //Ваш код здесь
    unset($_SESSION['TRUSTEDNET']);
    $tpl = file_get_contents(TRUSTED_MODULE_ROOT. "/../error.php");
    $message = "onOAuth2Exception" . PHP_EOL . $e . PHP_EOL;    
    $tpl2 = str_replace('${message}', $message, $tpl);
    echo($tpl2);
    die();
}
