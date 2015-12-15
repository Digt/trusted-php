<?php

require_once (__DIR__ . '/config.php');
require_once TRUSTED_MODULE_SIGN;

header('Content-Type: application/json; charset=' . LANG_CHARSET);

// ============================== AJAX Controller

$command = $_GET['command'];

$res = array("success" => false, "message" => "Param 'command' is needed");
if (isset($command)) {
    $params = $_POST;
    switch ($command) {
        case "sign":
            $res = AjaxSignCommand::sign($params, signDocuments);
            break;
        case "upload":
            $res = AjaxSignCommand::upload($params, uploadSignature);
            break;
        case "status":
            $res = AjaxSignCommand::status($params);
            break;
        case "updateStatus":
            $res = AjaxSignCommand::updateStatus($params, null);
            break;
        case "view":
            $res = AjaxSignCommand::view($params, viewSignature);
            break;
        case "content":
            $res = AjaxSignCommand::content($_GET);
            break;
        case "token":
            $res = AjaxSignCommand::token($_GET);
            break;
        default:
            $res = array("success" => false, "message" => "Unknown command '" . $command . "'");
    }
}
echo json_encode($res);
