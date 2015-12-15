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
    echo "onOAuth2Exception" . PHP_EOL;
    echo $e . message . PHP_EOL;
    unset($_SESSION['TRUSTEDNET']);
    die();
}

/**
 * Событие на возникновение ошибки Exception
 * @param \Exception $e
 */
function onException($e) {
    echo "onException" . PHP_EOL;
    echo $e . message . PHP_EOL;
    unset($_SESSION['TRUSTEDNET']);
    die();
}
