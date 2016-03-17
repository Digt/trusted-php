<?php

require_once (__DIR__ . '/config.php');
require_once(TRUSTED_MODULE_AUTH);

if (TRUSTED_DEBUG) {
    header("Content-Type: text/plain; charset=utf-8");
    echo 'DEBUG' . PHP_EOL;
    echo '========================================' . PHP_EOL;
    debug("GET", $_GET);
    debug("POST", $_POST);
}

function getParam($name, $default = null) {
    return __param($_GET, $name, $default);
}

function postParam($name, $default = null) {
    return __param($_POST, $name, $default);
}

function __param($array, $name, $default) {
    $res = $default;
    if (isset($array[$name])) {
        $res = $array[$name];
    }
    return $res;
}

try {
    if ($code = getParam("code")) {
        if (getParam("final", false)) {
            $res = TAuthCommand::getAccessTokenByCode($code);
            debug("OAuth token from service:", $res);
            $token = OAuth2::fromArray($res);
            debug($token);
            $user_array = TAuthCommand::getUserProfileByToken($token->getAccessToken());
            debug($user_array);
            if (TRUSTED_DB) {
                $user = TDataBaseUser::getUserById($user_array['id']);
                debug("TDataBaseUser::getUserById:", $user);
                if ($user) {
                    //Пользователь уже зарегистрирован
                    debug('Old user');
                    if (onRegUserFound) {
                        debug('Event onRegUserFound');
                        onRegUserFound($user);
                    }
                } else {
                    //Пользователь не найден
                    debug('New user');
                    $user = TUser::fromArray($user_array);
                    if (onBeforeUserInsert) {
                        debug('Event onBeforeUserInsert');
                        onBeforeUserInsert($user);
                    }
                    $user->save();
                }
            }
            $token->setUser($user);
            debug('Token', $token);
            if (onUserAuthorized) {
                debug('Event onUserAuthorized');
                onUserAuthorized($user);
            }
            header("Location: " . TRUSTED_AUTHORIZED_REDIRECT);
        } else {
            include_once("./widget.tpl");
        }
    } else {
        $token = OAuth2::getFromSession();
        if (!$token) {
            throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_TOKEN_NOT_FOUND, TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND, null);
        }
        $token->getAccessToken();
        $token->getUser();
        debug("Token", $token);
    }
} catch (OAuth2Exception $e) {
    if (onOAuth2Exception) {
        onOAuth2Exception($e);
    }
    debug("OAuth2Exception: " . $e->getMessage());
    debug($e->getTrace());
} catch (Exception $e) {
    if (Exception) {
        onException($e);
    }
    debug("Exception: " . $e->getMessage());
    debug($e->getTrace());
}

debug("END");
