<?php

require_once (__DIR__ . '/../config.php');

//path
define('TRUSTED_MODULE_SIGN_PATH', '/esign');
define('TRUSTED_MODULE_SIGN_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_SIGN_PATH);

//data base
define('TRUSTED_DB_TABLE_DOCUMENTS', 'trn_documents');
define('TRUSTED_DB_TABLE_DOCUMENTS_PROPERTY', TRUSTED_DB_TABLE_DOCUMENTS . '_property');
define('TRUSTED_DB_TABLE_DOCUMENTS_STATUS', TRUSTED_DB_TABLE_DOCUMENTS . '_status');

//Module URI
define('TRUSTED_URI_MODULE_SIGN', TRUSTED_URI_MODULE . TRUSTED_MODULE_SIGN_PATH);
define('TRUSTED_URI_AJAX_SIGN', TRUSTED_URI_MODULE_SIGN . '/ajax.php');

//TrustedNet URI
define('TRUSTED_SIGN_SET_STATUS', TRUSTED_COMMAND_APP . '/client/sign/setstatus');

/* Folder */
define('TRUSDET_SIGN_DOCS_PATH', '/trusted/esign');
define('TRUSDET_SIGN_DOCS_ROOT', TRUSTED_UPLOAD_ROOT . TRUSDET_SIGN_DOCS_PATH);
define('TRUSDET_SIGN_DOCS_URI', TRUSTED_UPLOAD_URI . TRUSDET_SIGN_DOCS_PATH);

//Типы документов
define('DOCUMENT_TYPE_FILE', 0);
define('DOCUMENT_TYPE_SIGNATURE', 1);

//Статусы документов
define('DOCUMENT_STATUS_DONE', 0);
define('DOCUMENT_STATUS_PROCESSING', 1);
define('DOCUMENT_STATUS_CANCEL', 2);
define('DOCUMENT_STATUS_ERROR', 3);

//Статус подписи
define("SIGN_STATUS_NEW", 0);
define("SIGN_STATUS_COMPLETE", 1);
define("SIGN_STATUS_CANCELED", -1);
