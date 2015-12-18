<?php

require_once (__DIR__ . '/../config.php');

//TrustedNet URI
define('TRUSTED_COMMAND_URI_OAUTH', TRUSTED_COMMAND_URI_HOST . '/idp/sso/oauth');
define('TRUSTED_COMMAND_URI_TOKEN', TRUSTED_COMMAND_URI_OAUTH . "/token");
define('TRUSTED_COMMAND_URI_LOGOUT', TRUSTED_COMMAND_URI_OAUTH . '/authorize/logout');
define('TRUSTED_COMMAND_URI_USERPROFILE', TRUSTED_COMMAND_URI_HOST . '/trustedapp/rest/person/profile/get');

//Module URI
define('TRUSTED_URI_MODULE_AUTH', TRUSTED_URI_MODULE . TRUSTED_MODULE_AUTH_PATH);

//OAuth params
define("TRUSTED_AUTH_REDIRECT_URI", TRUSTED_URI_MODULE_AUTH . "/authorize.php");

//Token status
define("TRUSTEDNET_AUTH_TOKEN_STATUS_ERROR", 0);
define("TRUSTEDNET_AUTH_TOKEN_STATUS_NOT_EXPIRED", 1);
define("TRUSTEDNET_AUTH_TOKEN_STATUS_EXPIRED", 2);

//========== Errors ==========
//messages
define("TRUSTEDNET_ERROR_MSG_TOKEN_NOT_FOUND", "Token is not found");
define("TRUSTEDNET_ERROR_MSG_DIFFERENT_USER_ID", "Id of ServiceUser and TUser is different");
define("TRUSTEDNET_ERROR_MSG_CURL", "Wrong CURL request");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_NO_EMAIL", "User has not got email");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_CREATE", "Error on User account create");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_HAS_EMAIL", "User account has such email");

//codes
define("TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND", 1);
define("TRUSTEDNET_ERROR_CODE_DIFFERENT_USER_ID", 2);
define("TRUSTEDNET_ERROR_CODE_CURL", 3);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_NO_EMAIL", 4);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_CREATE", 5);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_HAS_EMAIL", 6);
