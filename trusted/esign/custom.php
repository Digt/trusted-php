<?php

/**
 * Функция вызывается перед отправкой запроса на подпись на сервис `trusted`
 * @param \DocumentCollection $docs Коллекция подписываемых документов
 * @param \AjaxParams $params
 */
function signDocuments(&$docs, $params) {
    
}

/**
 * Функция вызывается перед отправкой запроса на просмотр подписи на сервис `trusted`
 * @param \Document $doc
 * @param \AjaxParams $params
 */
function viewSignature($doc, $params) {
    
}

function throwError($msg) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(array(
        "success" => false,
        "message" => $msg
    ));
    die();
}

/**
 * Функция вызывается перед тем как отдать содержимое файла
 * @param \Document $doc            Документ
 * @param string    $accessToken    Ключ доступа пользователя
 */
function getContent($doc, $accessToken) {
    $ext = (($doc->getType() == 1 ) ? ".p7s" : "");
    $doc->setName($doc->getName() . $ext);
}

/**
 * 
 * @param \Document $doc            Документ
 * @param type      $accessToken    Ключ доступа пользователя
 * @return boolean
 */
function beforeUploadSignature($doc, $accessToken) {
    return true;
}

/**
 * 
 * @param \Document $doc        Документ
 * @param mixed     $file       Временный файл загруженный на сервер
 * @param mixed     extra       Дополнительные параметры
 */
function uploadSignature($doc, $file, $extra = null) {
    if ($doc->getParent()->getType() == DOCUMENT_TYPE_FILE) {
        $doc->setSysName($doc->getSysName() . '.p7s');
        $doc->setPath($doc->getPath() . '.p7s');
    }
    copy($file['tmp_name'], urldecode($doc->getPath()));
}

function updateDocumentStatus(){
    
}

class TSignUtils {

    /**
     * Создает новый Документ. При создании нового Документа добавляется запись в БД.
     * @param string $file Путь к файлу
     * @param boolean $copy Если значение параметра Истина, то файл будет скопирован в папку модуля trustednetsiger
     * @param string $name Пользовательское имя файла
     * @param string $type Тип файла. По умолчанию DOCUMENT_TYPE_DOCUMENT
     * @return \Document
     */
    public static function createDocument($file, $copy, $name = null, $type = DOCUMENT_TYPE_FILE) {
        $sysName = CDirectory::getFileName($file);
        echo($sysName . PHP_EOL);
        if ($copy && CDirectory::exists($file) && !is_dir($file)) {
            print_r("Copy file" . PHP_EOL);
            print_r(TRUSDET_SIGN_DOCS_ROOT . PHP_EOL);
            if (!CDirectory::exists(TRUSDET_SIGN_DOCS_ROOT)) {
                print_r("Create folder" . PHP_EOL);
                CDirectory::create(TRUSDET_SIGN_DOCS_ROOT);
            }
            $new_path = TRUSDET_SIGN_DOCS_ROOT . '/' . $sysName;

            copy($file, $new_path);
            //unlink($file);
            $file = $new_path;
        }
        $name = $name ? $name : $sysName;
        $doc = new Document();
        $doc->setPath(str_replace($sysName, urlencode($sysName), $file));
        $doc->setName($name);
        $doc->setSysName($sysName);
        $doc->setType($type);
        // $doc->getProperties()->add(new Property($doc->getId(), "STATUS", "NONE"));
        // $props = $doc->getProperties();
        // $props->add(new Property(null, "ORDER", $orderId));
        print_r($doc);
        $doc->save();

        return $doc;
    }

}
