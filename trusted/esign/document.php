<?php

require_once (__DIR__ . '/config.php');
require_once TRUSTED_MODULE_AUTH;
require_once TRUSTED_MODULE_SIGN_ROOT . "/custom.php";

$DB = null;

if (TRUSTED_DB) {
    $DB = new TDataBase();
    $r = $DB->Connect(TRUSTED_DB_HOST, TRUSTED_DB_NAME, TRUSTED_DB_LOGIN, TRUSTED_DB_PASSWORD);
}

/**
 * Класс для работы с БД
 */
class TDataBaseDocument {

    /**
     * Сохраняет родителя документа
     * @param \Document $doc Документ с родителем
     * @param number $id Идентификатор документа. По умолчанию NULL
     */
    protected static function saveDocumentParent($doc, $id = null) {
        if ($doc->getParent()) {
            $parent = $doc->getParent();
            $parent->setChildId($id);
            $parent->save();
        }
    }

    /**
     * Возвращает коллекцию конечных документов. В список попадают только те документы
     * у которых отсутствуют потомки.
     * @global type $DB
     * @return \DocumentCollection Коллекция документов
     */
    static function getDocuments() {
        global $DB;
        $sql = 'SELECT * FROM ' . TRUSTED_DB_TABLE_DOCUMENTS . ' WHERE CHILD_ID is null';
        $rows = $DB->Query($sql);
        $res = new DocumentCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Document::fromArray($array));
        }
        return $res;
    }

    /**
     * Возвращает MySQL объект с документами по заданному фильтру
     * @global type $DB
     * @param type $filter Array
     * @return mysqli_result Object
     */
    static function getIdDocumentsByFilter($filter) {
        $find_docId = $filter['DOC'];
        $find_fileName = $filter['FILE_NAME'];
        $find_signInfo = $filter['SIGN'];
        $find_status = $filter['STATUS'];

        global $DB;
        $sql = "    
            SELECT
                TD.ID, TDS.STATUS
            FROM 
                trn_documents TD LEFT JOIN 
                trn_documents_status TDS 
            ON
                TD.ID = TDS.DOCUMENT_ID 
            WHERE
                isnull(TD.CHILD_ID)";
        if ($find_docId)
            $sql .=" AND TD.ID = " . $find_docId;
        if ($find_fileName)
            $sql .=" AND TD.ORIGINAL_NAME LIKE '%" . $find_fileName . "%'";
        if ($find_signInfo)
            $sql .=" AND TD.SIGNERS LIKE '%" . $DB->EscapeString($find_signInfo) . "%'";
        if ($find_status != "")
            $sql .=" AND TDS.STATUS = " . $find_status;
        $sql .= " ;";

        $rows = $DB->Query($sql);
        return $rows;
    }

    /**
     * Возвращает статус документа
     * @global type $DB
     * @param \Document $doc
     * @return \DocumentStatus
     */
    static function getStatus($doc) {
        return TDataBaseDocument::getStatusById($doc->getId());
    }

    /**
     * Возвращает Статус по идентификатору документа
     * @global type $DB
     * @param number $docId
     * @return \DocumentStatus
     */
    static function getStatusById($docId) {
        global $DB;
        $sql = 'SELECT * FROM ' . TRUSTED_DB_TABLE_DOCUMENTS_STATUS . ' WHERE DOCUMENT_ID = ' . $docId;
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        $res = null;
        if ($array) {
            $res = DocumentStatus::fromArray($array);
            $res->setDocumentId($docId);
        }
        return $res;
    }

    /**
     * Добавляет новый статус в таблицу
     * @global type $DB
     * @param \DocumentStatus $status
     */
    static function insertStatus($status) {
        global $DB;
        $sql = 'INSERT INTO ' . TRUSTED_DB_TABLE_DOCUMENTS_STATUS . '  '
                . '(DOCUMENT_ID, STATUS) VALUES ('
                . $status->getDocumentId() . ', '
                . $status->getValue()
                . ')';
        $DB->Query($sql);
    }

    /**
     * 
     * @global type $DB
     * @param \DocumentStatus $status
     */
    static function saveStatus($status) {
        if (!TDataBaseDocument::getStatus($status->getDocument())) {
            TDataBaseDocument::insertStatus($status);
        } else {
            global $DB;
            $sql = 'UPDATE ' . TRUSTED_DB_TABLE_DOCUMENTS_STATUS . ' SET '
                    . 'STATUS = ' . $status->getValue() . ', '
                    . 'CREATED = CURRENT_TIMESTAMP '
                    . 'WHERE DOCUMENT_ID = ' . $status->getDocumentId();
            $DB->Query($sql);
        }
    }

    static function removeStatus($status) {
        global $DB;
        $sql = 'DELETE FROM ' . TRUSTED_DB_TABLE_DOCUMENTS_STATUS
                . ' WHERE DOCUMENT_ID = ' . $status->getDocumentId();
        $DB->Query($sql);
    }

    /**
     * Сохраняет документ в БД. Если у документа отсутствует идентификатор, то добавляет
     * документ в БД.
     * @global type $DB
     * @param \Document $doc
     */
    static function saveDocument($doc) {
        if ($doc->getId() == null) {
            TDataBaseDocument::insertDocument($doc);
        } else {
            global $DB;
            $parentId = $doc->getParentId();
            $childId = $doc->getChildId();
            if (is_null($parentId)) {
                $parentId = 'NULL';
            }
            if (is_null($childId)) {
                $childId = 'NULL';
            }
            $sql = 'UPDATE ' . TRUSTED_DB_TABLE_DOCUMENTS . ' SET '
                    . 'ORIGINAL_NAME = "' . $DB->EscapeString($doc->getName()) . '", '
                    . 'SYS_NAME = "' . $DB->EscapeString($doc->getSysName()) . '", '
                    . 'PATH = "' . $doc->getPath() . '", '
                    . 'TYPE = ' . $doc->getType() . ', '
                    . "SIGNERS = '" . $DB->EscapeString($doc->getSigners()) . "', "
                    . 'PARENT_ID = ' . $parentId . ', '
                    . 'CHILD_ID = ' . $childId . ' '
                    . 'WHERE ID = ' . $doc->getId();
            $DB->Query($sql);
            TDataBaseDocument::saveDocumentParent($doc, $doc->getId());
        }
    }

    /**
     * Добавляет новый документ в БД
     * @global \TDataBase $DB
     * @param \Document $doc
     */
    static function insertDocument($doc) {
        global $DB;
        $parentId = $doc->getParentId();
        $childId = $doc->getChildId();
        if (is_null($parentId)) {
            $parentId = 'NULL';
        }
        if (is_null($childId)) {
            $childId = 'NULL';
        }
        $sql = 'INSERT INTO ' . TRUSTED_DB_TABLE_DOCUMENTS . '  '
                . '(ORIGINAL_NAME, SYS_NAME, PATH, SIGNERS, TYPE, PARENT_ID, CHILD_ID)'
                . 'VALUES ('
                . '"' . $DB->EscapeString($doc->getName()) . '", '
                . '"' . $DB->EscapeString($doc->getSysName()) . '", '
                . '"' . $doc->getPath() . '", '
                . "'" . $DB->EscapeString($doc->getSigners()) . "', "
                . $doc->getType() . ', '
                . $parentId . ', '
                . $childId
                . ')';
        $DB->Query($sql);
        $doc->setId($DB->LastID());
        TDataBaseDocument::saveDocumentParent($doc, $doc->getId());
    }

    /**
     * Удаляет документ из БД
     * @global \TDataBase $DB
     * @param \Document $doc
     */
    static function removeDocument(&$doc) {
        global $DB;
        $sql = 'DELETE FROM ' . TRUSTED_DB_TABLE_DOCUMENTS . '  '
                . 'WHERE ID = ' . $doc->getId();
        $DB->Query($sql);
        TDataBaseDocument::saveDocumentParent($doc);
    }

    /**
     * Получает документ из БД по заданному идентификатору.
     * @global type $DB
     * @param number $id Идентификатор документа
     * @return \Document
     */
    static function getDocumentById($id) {
        global $DB;
        $sql = 'SELECT * FROM ' . TRUSTED_DB_TABLE_DOCUMENTS . ' WHERE ID = ' . $id;
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        $res = Document::fromArray($array);
        return $res;
    }

    /**
     * Возвращает последний документ по имени
     * @global type $DB
     * @param string $name Имя документа
     * @return \Document
     */
    static function getDocumentByName($name) {
        global $DB;
        $sql = 'SELECT * FROM ' . TRUSTED_DB_TABLE_DOCUMENTS . ' WHERE ORIGINAL_NAME = "' . $DB->EscapeString($name) . '"';
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        $res = null;
        if ($array) {
            $res = Document::fromArray($array)->getLastDocument();
        }
        return $res;
    }

    /**
     * Сохраняет свойство в БД. Если идентификатор Свойств (ID) пустой, то 
     * добавляет запись в таблицу
     * @global type $DB
     * @param \Property $property Свойство
     * @param string $tableName название таблицы
     */
    static function saveProperty($property, $tableName) {
        if ($property->getId() == null) {
            TDataBaseDocument::insertProperty($property, $tableName);
        } else {
            global $DB;
            $sql = 'UPDATE ' . $tableName . ' SET PARENT_ID = ' . $property->getParentId() . ', TYPE="' . $DB->EscapeString($property->getType()) . '", VALUE="' . $DB->EscapeString($property->getValue()) . '" WHERE ID = ' . $property->getId();
            $DB->Query($sql);
        }
    }

    /**
     * Добавляет новое свойство БД.
     * @global type $DB
     * @param \Property $property
     * @param string $tableName
     */
    static function insertProperty($property, $tableName) {
        global $DB;
        $sql = 'INSERT INTO ' . $tableName . ' (PARENT_ID, TYPE, VALUE) VALUES (' . $property->getParentId() . ', "' . $DB->EscapeString($property->getType()) . '", "' . $DB->EscapeString($property->getValue()) . '")';
        $DB->Query($sql);
        $property->setId($DB->LastID());
    }

    /**
     * Получает коллекцию Свойств из БД по значению поля.
     * @global type $DB
     * @param string $tableName Название таблицы
     * @param string $fldName Имя поля
     * @param string $value Значение поля
     * @return \PropertyCollection
     */
    static function getPropertiesBy($tableName, $fldName, $value) {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE  ' . $fldName . ' = "' . $DB->EscapeString($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Получает коллекцию Свойств по заданному значению поля
     * @global type $DB
     * @param string $tableName Название таблицы
     * @param string $type Значение поля TYPE
     * @param string $value Значение поля VALUE
     * @return \PropertyCollection
     */
    static function getPropertiesByType($tableName, $type, $value) {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE TYPE="' . $DB->EscapeString($type) . '" AND VALUE = "' . $DB->EscapeString($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Получает свойство по заданному значению
     * @global type $DB
     * @param string $tableName Название таблицы
     * @param string $fldName Название поля
     * @param string $value Значение поля
     * @return \Property
     */
    static function getPropertyBy($tableName, $fldName, $value) {
        $props = TDataBaseDocument::getPropertiesBy($tableName, $fldName, $value);
        $res = null;
        if ($props->count()) {
            $res = $props->items(0);
        }
        return $res;
    }

    /**
     * Получает коллекцию Свойств по идентификатору родителя
     * @param string $tableName Название таблицы
     * @param number $parentId Идентификатор родителя
     * @return PropertyCollection
     */
    static function getPropertiesByParentId($tableName, $parentId) {
        return TDataBaseDocument::getPropertiesBy($tableName, 'PARENT_ID', $parentId);
    }

}

interface IEntity {

    /**
     * Преобразует Массив данных в объект
     * @param type $array
     */
    static function fromArray($array);

    /**
     * Преобразует Объект в Массив данных
     */
    function toArray();
}

interface ISave {

    /**
     * Сохраняет Объект в БД
     */
    function save();
}

/**
 * Класс Свойство
 */
class Property implements IEntity, ISave {

    protected $parentId;
    protected $id;
    protected $type;
    protected $value;

    /**
     * Конструктор
     * @param number $id Идентификатор родителя
     * @param string $type Название свойства
     * @param string $value Значение свойства
     */
    function __construct($id = null, $type = null, $value = '') {
        $this->parentId = $id;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Возвращает идентификатор
     * @return type
     */
    function getId() {
        return $this->id;
    }

    /**
     * Задает идентификатор
     * @param number $id
     */
    function setId($id) {
        $this->id = $id;
    }

    /**
     * Задает идентификатор родителя
     * @param number $docId Идентификатор родителя
     */
    function setParentId($docId) {
        $this->parentId = $docId;
    }

    /**
     * Возвращает идентификатор родителя
     * @return number
     */
    function getParentId() {
        return $this->parentId;
    }

    /**
     * Задает имя свойства
     * @param string $type Имя свойства
     */
    function setType($type) {
        $this->type = $type;
    }

    /**
     * Возвращает имя свойства
     * @return string
     */
    function getType() {
        return $this->type;
    }

    /**
     * Задает значение свойства
     * @param string $value
     */
    function setValue($value) {
        $this->value = $value;
    }

    /**
     * Возвращает значение свойства
     * @return string
     */
    function getValue() {
        return $this->value;
    }

    /**
     * Возвращает Свойство из Массива данных
     * @param mixed $array
     * @return \Property
     */
    public static function fromArray($array) {
        $res = new Property();
        $res->id = $array["ID"];
        $res->parentId = $array["PARENT_ID"];
        $res->type = $array["TYPE"];
        $res->value = $array["VALUE"];
        return $res;
    }

    /**
     * Получает Массив данных из Свойства
     * @return type
     */
    public function toArray() {
        $res = array(
            "ID" => $this->id,
            "PARENT_ID" => $this->parentId,
            "TYPE" => $this->type,
            "VALUE" => $this->value
        );
        return $res;
    }

    /**
     * Добавляет/Сохраняет Свойство в БД
     */
    public function save() {
        TDataBaseDocument::saveProperty($this, TRUSTED_DB_TABLE_DOCUMENTS_PROPERTY);
    }

}

/**
 * Класс коллекции свойств
 */
class PropertyCollection extends Collection {

    /**
     * Возвращает Свойство из коллекции по индексу [0..n]
     * @param number $i
     * @return \Property
     */
    function items($i) {
        return parent::items($i);
    }

    /**
     * Возвращает Свойство по названию типа
     * @param type $type Название типа
     * @return \Property
     */
    function getItemByType($type) {
        $list = $this->getList();
        $res = null;
        foreach ($list as $item) {
            if ($item->getType() == $type) {
                $res = $item;
                break;
            }
        }
        return $res;
    }

}

/**
 * Класс коллекции
 */
class Collection {

    protected $items_ = array();

    /**
     * Возвращает массив Элементов
     * @return mixed
     */
    function getList() {
        return $this->items_;
    }

    /**
     * Добавляет элемент в коллекцию
     * @param type $item
     */
    public function add($item) {
        if (isset($item)) {
            $this->items_[] = $item;
        }
    }

    /**
     * Возвращает Элемент из коллекции
     * @param type $i Индекс Элемента в коллекции [0..n]
     * @return type
     */
    public function items($i) {
        return $this->items_[$i];
    }

    /**
     * Возвращает колличество Элементов в коллекции
     * @return number
     */
    public function count() {
        return count($this->items_);
    }

}

class DocumentStatus implements IEntity, ISave {

    protected $status;
    protected $documentId;
    protected $document;
    protected $created;

    function setValue($status) {
        $this->status = $status;
    }

    function getValue() {
        return $this->status;
    }

    function getCreated() {
        return $this->created;
    }

    function getDocumentId() {
        return $this->documentId;
    }

    function setDocumentId($docId) {
        $this->documentId = $docId;
        $this->document = null;
    }

    function setDocument($doc) {
        $this->document = $doc;
        $this->documentId = $doc->getId();
    }

    function getDocument() {
        if (!$this->document && !is_null($this->documentId)) {
            $this->document = TDataBaseDocument::getDocumentById($this->documentId);
        }
        return $this->document;
    }

    public function toArray() {
        
    }

    public static function fromArray($array) {
        $res = new DocumentStatus();
        $res->documentId = $array["DOCUMENT_ID"];
        $res->status = $array["STATUS"];
        $res->created = $array["CREATED"];
        return $res;
    }

    public function save() {
        TDataBaseDocument::saveStatus($this);
    }

    static function create($doc, $value) {
        $status = TDataBaseDocument::getStatus($doc);
        if (!$status) {
            $status = new DocumentStatus();
            $status->documentId = $doc->getId();
            $status->document = $doc;
        }
        $status->status = $value;
        TDataBaseDocument::saveStatus($status);
        return $status;
    }

}

/**
 * Класс документ
 */
class Document implements IEntity, ISave {

    protected $id = null;
    protected $created;
    protected $name = '';
    protected $sysName = '';
    protected $path = '';
    protected $type = DOCUMENT_TYPE_FILE;
    protected $signers = '';
    protected $properties = null;
    protected $parent = null;
    protected $parentId = null;
    protected $child = null;
    protected $childId = null;
    protected $status = null;

    /**
     * Возвращает Статус Документа
     * @return \DocumentStatus
     */
    function getStatus() {
        if (!$this->status) {
            $this->status = TDataBaseDocument::getStatus($this);
        }
        return $this->status;
    }

    /**
     * Задает Статус Документа
     * @param \DocumentStatus $status
     */
    function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Задает идентификатор потомка Документа
     * @param type $childId
     */
    function setChildId($childId) {
        $this->childId = $childId;
        $this->child = null;
    }

    /**
     * Возвращает идентификатор потомка Документа
     * @return type
     */
    function getChildId() {
        return $this->childId;
    }

    /**
     * Возвращает ИСТИНА если Документ имеет Потомка
     * @return boolean
     */
    function hasChild() {
        $res = false;
        $child = $this->getChild();
        if ($child) {
            $res = true;
        }
        return $res;
    }

    /**
     * Возвращает последний Документ в цепочке
     * @return \Document
     */
    function getLastDocument() {
        $res = $this;
        if ($res->hasChild()) {
            $child = $this->getChild();
            $res = $child->getLastDocument();
        }
        return $res;
    }

    /**
     * Задает потомка Документа
     * @param \Document $doc
     */
    function setChild($doc) {
        $this->child = $doc;
        $this->childId = $doc->id;
    }

    /**
     * Возвращает потомка Документа. 
     * @return \Document
     */
    function getChild() {
        if (!$this->child && $this->childId) {
            $this->child = TDataBaseDocument::getDocumentById($this->childId);
        }
        return $this->child;
    }

    /**
     * Задает ID родительского документа
     * @param number $parentId
     */
    function setParentId($parentId) {
        $this->parentId = $parentId;
        $this->parent = null;
    }

    /**
     * Возвращает ID родительского документа
     * @return number
     */
    function getParentId() {
        return $this->parentId;
    }

    /**
     * Задает родительский документ
     * @param \Document $parent
     */
    function setParent($parent) {
        $this->parent = $parent;
        $this->parentId = $parent->id;
    }

    /**
     * Возвращает родительский документ
     * @return \Document
     */
    function getParent() {
        if (!$this->parent && $this->parentId) {
            $this->parent = TDataBaseDocument::getDocumentById($this->parentId);
        }
        return $this->parent;
    }

    /**
     * Возвращает коллекцию свойств документа
     * @param number $i
     * @return PropertyCollection | Property
     */
    function getProperties($i = null) {
        $props = &$this->properties;
        if (!$props) {
            if ($this->getId()) {
                $props = TDataBaseDocument::getPropertiesByParentId(TRUSTED_DB_TABLE_DOCUMENTS_PROPERTY, $this->getId());
            } else {
                $props = new PropertyCollection();
            }
        }
        $res = $props;
        if (!is_null($i)) {
            $res = $props->items($i);
        }
        return $res;
    }

    /**
     * Возвращает идентификатор. Связано с полем ID
     * @return type
     */
    function getId() {
        return $this->id;
    }

    /**
     * Задает индентификатор. Связано с полем ID
     */
    function setId($id) {
        $this->id = $id;
    }

    /**
     * Возвращает время создания документа. Связано с полем TIMESTAMP_X
     * @return type Время
     */
    function getCreated() {
        return $this->created;
    }

    /**
     * Задает время создания документа. Связано с полем TIMESTAMP_X
     * @param type $time Время
     */
    function setCreated($time) {
        $this->created = $time;
    }

    /**
     * Возвращает имя документа. Связано с полем ORIGINAL_NAME
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * Задает имя документа. Связано с полем ORIGINAL_NAME
     * @param string $name Имя документа
     */
    function setName($name) {
        $this->name = $name;
    }

    /**
     * Возвращает системное имя документа. Связано с полем SYS_NAME
     * @return string
     */
    function getSysName() {
        return $this->sysName;
    }

    /**
     * Задает системное имя документа. Связано с полем SYS_NAME
     * @param string $desc
     */
    function setSysName($desc) {
        $this->sysName = $desc;
    }

    public function getHtmlPath() {
        return str_replace(TRUSTED_PROJECT_ROOT, "", $this->path);
    }

    /**
     * Возвращает путь хранения документа. Связано с полем PATH
     * @return string
     */
    function getPath() {
        return $this->path;
    }

    /**
     * Задает путь храниения документа. Связано с полем PATH
     * @param string $path
     */
    function setPath($path) {
        $this->path = $path;
    }

    /**
     * возвращает тип документа. Связано с полем DOCUMENT_TYPE
     * @return number
     */
    function getType() {
        return $this->type;
    }

    /**
     * Задает тип документа. Связано с полем DOCUMENT_TYPE
     * @param number $type
     */
    function setType($type) {
        $this->type = $type;
    }

    /**
     * Возвращает подписчиков документа. Связано с полем SIGNERS
     * @return string Строка в JSON формате
     */
    function getSigners() {
        return $this->signers;
    }

    /**
     * Возвращает информацию о подписи файла в виде массива
     * @return Array
     */
    function getSignersToArray() {
        $singers = $this->signers;
        $singers = explode(",{", $singers);
        foreach ($singers as $key => $singer) {
            $arr = array("{", "}", "[", "]");
            $arrTo = array("", "", "", "");
            $singer = str_replace($arr, $arrTo, $singer);
            $singer = explode(",", $singer);
            foreach ($singer as $keyN => $value) {
                $value = str_replace('"', '', $value);
                $value = explode(":", $value);
                $prop = $value[0];
                $value = $value[1];
                $singer[$prop] = $value;
                unset($singer[$keyN]);
            }
            $singers[$key] = $singer;
            if ($singer["subjectName"]) {
                $subject = explode("/", ($singer["subjectName"]));
                foreach ($subject as $keyS => $value) {
                    $value = explode("=", $value);
                    $prop = $value[0];
                    $value = $value[1];
                    $subject[$prop] = $value;
                    unset($subject[$keyS]);
                }
                $singer["subjectName"] = $subject;
            }
            if ($singer["issuerName"]) {
                $subject = explode("/", ($singer["issuerName"]));
                foreach ($subject as $keyS => $value) {
                    $value = explode("=", $value);
                    $prop = $value[0];
                    $value = $value[1];
                    $subject[$prop] = $value;
                    unset($subject[$keyS]);
                }
                $singer["issuerName"] = $subject;
            }
            $singers[$key] = $singer;
        }
        return $singers;
    }

    /**
     * Задает подписичков документа. Связано с полем SIGNERS
     * @param string $signers Строка JSON формате
     */
    function setSigners($signers) {
        $this->signers = $signers;
    }

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * Возвращает новый экземпляр документа из асоциативного массива.
     * @param type $array
     * @return \Document
     */
    static function fromArray($array) {
        $doc = null;
        if ($array) {
            $doc = new Document();
            $doc->setId($array["ID"]);
            $doc->setCreated($array["TIMESTAMP_X"]);
            $doc->setName($array["ORIGINAL_NAME"]);
            $doc->setSysName($array["SYS_NAME"]);
            $doc->setPath($array["PATH"]);
            $doc->setSigners($array["SIGNERS"]);
            $doc->setType($array["TYPE"]);
            $doc->setParentId($array["PARENT_ID"]);
            $doc->setChildId($array["CHILD_ID"]);
        }
        return $doc;
    }

    public function jsonSerialize() {
        $a = array(
            "name" => $this->name,
            "url" => $this->getHtmlPath(),
            "id" => $this->getId(),
            "sys_name" => $this->name
        );
        return $a;
    }

    public function toJSON() {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Удаляет Документ.
     * @return boolean
     */
    public function remove() {
        TDataBaseDocument::removeDocument($this);
    }

    /**
     * Сохраняет измененный документ. Если ИД документа пустой, то добавляет новую запись в БД.
     * @return boolean
     */
    public function save() {
        TDataBaseDocument::saveDocument($this);
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            if (!$prop->getParentId()) {
                $prop->setParentId($this->id);
            }
            $prop->save();
        }
    }

    /**
     * 
     * @return \Document
     */
    public function copy() {
        $new = new Document();
        $new->setName($this->getName());
        $new->setPath($this->getPath());
        $new->setSigners($this->getSigners());
        $new->setSysName($this->getSysName());
        $new->setType($this->getType());
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            $newProp = new Property(null, $prop->getType(), $prop->getValue());
            $new->getProperties()->add($newProp);
        }
        return $new;
    }

    public function toArray() {
        
    }

}

class DocumentCollection extends Collection implements IEntity {

    /**
     * Возвращает элемент коллекции по индексу
     * @param number $i Число [0..n]
     * @return DocumentItem Документ
     */
    function items($i) {
        return parent::items($i);
    }

    public function jsonSerialize() {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = $item->jsonSerialize();
        }
        return $a;
    }

    public function toJSON() {
        return json_encode($this->jsonSerialize());
    }

    static function fromArray($array) {
        $docs = new DocumentCollection();
        foreach ($array as &$item) {
            $docs->add(Document::fromArray($item));
        }
        return $docs;
    }

    public function toArray() {
        
    }

}

//========== AJAX ==========
class AjaxParams implements IEntity {

    protected $logo = null;
    protected $extra = "";
    protected $css = null;

    function getLogo() {
        return $this->logo;
    }

    function setLogo($logo) {
        $this->logo = $logo;
    }

    function getCss() {
        return $this->css;
    }

    function setCss($css) {
        $this->css = $css;
    }

    function getExtra() {
        return $this->extra;
    }

    function setExtra($extra) {
        $this->extra = $extra;
    }

    public function toArray() {
        $res = array();
        foreach ($this as $key => $value) {
            if ($value) {
                $res[$key] = $value;
            }
        }
        return $res;
    }

    protected static function fromArrayItem($array, $name) {
        $res = null;
        if (isset($array[$name])) {
            $res = $array[$name];
        }
        return $res;
    }

    public static function fromArray($array) {
        $res = new AjaxParams();
        foreach ($array as $key => $value) {
            foreach ($res as $okey => &$ovalue) {
                if ($okey == $key) {
                    $ovalue = $value;
                }
            }
        }
        return $res;
    }

}

class AjaxSign {

    static protected function getToken() {
        try {
            $token = OAuth2::getFromSession();
            if (!$token) {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Отсутствует авторизация на сервисе TrustedNET.\n\nПройдите авторизацию на сервисе TrustedNET из личного кабинета пользователя.",
                    "code" => 1));
                die();
            }
        } catch (OAuth2Exception $e) {
            echo json_encode(array(
                "success" => false,
                "message" => $e->getMessage(),
                "code" => 0));
            die();
        }
        return $token;
    }

    static protected function getAccessToken() {
        return AjaxSign::getToken()->getAccessToken();
    }

    static protected function getRefreshToken() {
        return AjaxSign::getToken()->getRefreshToken();
    }

    static function sendRequestClient($command, $json) {
        $url = TRUSTED_COMMAND_REST . '/' . $command;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . AjaxSign::getAccessToken()));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        $data = array('data' => $json);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 500) {
                $result = json_encode(array("success"=>false, "message" => "Баланс приложения равен 0"));
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            debug("CURL error", $error);
            throw new Exception("CURL Error", null);
        }
        curl_close($curl);

        return $result;
    }

    /**
     * 
     * @param \DocumentCollection $docs
     * @param \AjaxParams $params
     */
    static function sendSignRequest($docs, $params = null) {
        $docsList = $docs->getList();
        $files = array();
        $rToken = AjaxSign::getRefreshToken();
        foreach ($docsList as &$doc) {
            $file = array("file" => $doc->jsonSerialize());
            $file["file"]["url"] = TRUSTED_URI_AJAX_SIGN . '?command=content&id=' . $doc->getId() . '&token=' . $rToken;
            $files[] = $file;
        }
        $data = array(
            "files" => $files,
            "uploader" => TRUSTED_URI_AJAX_SIGN . "?command=upload",
            "cancel" => TRUSTED_URI_AJAX_SIGN . "?command=updateStatus&status=2",
            "error" => TRUSTED_URI_AJAX_SIGN . "?command=updateStatus&status=3",
            "token" => $rToken
        );
        //Добавить поля из AjaxArray
        if ($params) {
            $list = $params->toArray();
            foreach ($list as $key => $value) {
                $data[$key] = $value;
            }
        }
        //Подкотовка данных запроса
        $json = json_encode($data);
        return AjaxSign::sendRequestClient("client/sign", $json);
    }

    static function sendViewRequest($doc, $params = null) {
        $file = $doc->jsonSerialize();
        $rToken = AjaxSign::getRefreshToken();
        $file["url"] = $file["file"]["url"] = TRUSTED_URI_AJAX_SIGN . '?command=content&id=' . $doc->getId() . '&token=' . $rToken;
        $data = array(
            "file" => $file,
            "token" => AjaxSign::getRefreshToken()
        );
        //Добавить поля из AjaxArray
        if ($params) {
            $list = $params->toArray();
            foreach ($list as $key => $value) {
                $data[$key] = $value;
            }
        }
        //Подкотовка данных запроса
        $json = json_encode($data);
        return AjaxSign::sendRequestClient("client/view", $json);
    }

    static function sendSetStatus($operationId, $status = 1, $desc = "") {
        $data = array(
            "data" => $operationId,
            "status" => $status,
            "description" => $desc,
            "clientId" => TRUSTED_LOGIN_CLIENT_ID,
            "secret" => TRUSTED_LOGIN_CLIENT_SECRET
        );
        //print_r($data);
        //Подкотовка данных запроса
        $url = TRUSTED_SIGN_SET_STATUS;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if ($info['http_code'] == 200) {
                
            } else {
                //echo "Wrong HTTP response status " . $info['http_code'] . PHP_EOL;
                echo json_encode(array("success" => false, "message" => $result));
                die();
            }
        } else {
            curl_close($curl);
            $error = curl_error($curl);
            echo json_encode(array("success" => false, "message" => $error));
            die();
        }
        return $result;
    }

}

class AjaxSignCommand {

    static function updateStatus($params) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.updateStatus");
        $id = $params["id"];
        $res["data"] = $id;
        $doc = TDataBaseDocument::getDocumentById($id);
        if (!$doc) {
            $res['message'] = "Документ с заданным ID не существует";
            return $res;
        }
        $status = $_GET["status"];
        if ($doc->getStatus() && $doc->getStatus()->getValue() == DOCUMENT_STATUS_PROCESSING) {
            switch ($status) {
                case DOCUMENT_STATUS_CANCEL:
                    $doc->getStatus()->setValue($status);
                    $doc->getStatus()->save();
                    AjaxSign::sendSetStatus($params["operationId"], -1, "Canceled");
                    $res['success'] = true;
                    break;
                case DOCUMENT_STATUS_ERROR:
                    $doc->getStatus()->setValue($status);
                    $doc->getStatus()->save();
                    AjaxSign::sendSetStatus($params["operationId"], -1, "Error");
                    $res['success'] = true;
                    break;
                default:
                    $res['message'] = 'Не известный статус документа';
            }
        } else {
            $res['message'] = 'Статус для Документа был изменен ранее';
        }
        return $res;
    }

    /**
     * 
     * @param mixed $params {id: Array(Number), logo: String, extra: String}
     * @param function $cb 
     * @return mixed {success: Boolean; message: String}
     */
    static function sign($params, $cb = signDocuments) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.sign");
        $docsId = $params["id"];
        if (isset($docsId)) {
            $docs = new DocumentCollection();
            foreach ($docsId as &$id) {
                $doc = TDataBaseDocument::getDocumentById($id);
                if($doc!==null) {
                    $lastDoc = $doc->getLastDocument();
                    //Проверка статуса Документа. Если Документ имеет статус "В процессе", то вернуть пользователю ошибку
                    if ($lastDoc->getStatus() && $lastDoc->getStatus()->getValue() == DOCUMENT_STATUS_PROCESSING) {
                        $res["message"] = "Один из документов был отправлен на подпись. Повторите попытку позже.";
                        return $res;
                    }
                    $docs->add($lastDoc);
                }
            }
            $ajaxParams = AjaxParams::fromArray($params);
            if ($cb) {
                $cb($docs, $ajaxParams);
            }
            if ($docs->count()) {
                $resp = json_decode(AjaxSign::sendSignRequest($docs, $ajaxParams), true);

                if ($resp["success"] == true) {
                    $res["success"] = true;
                    $res["code"] = $resp["code"];
                    $res["message"] = $resp['message'];
                    $list = $docs->getList();
                    foreach ($list as $item) {
                        //Выставление статуса документа для его блокировки
                        DocumentStatus::create($item, DOCUMENT_STATUS_PROCESSING);
                    }
                } else {
                    $res["message"] = isset($resp["message"]) ? $resp["message"] : $resp["error"];
                    $res["code"] = $resp["code"];
                }
            } else {
                $res["message"] = "Нет документов требующих подписи";
            }
        } else {
            $res["message"] = "POST parameter 'id' is required";
        }
        return $res;
    }

    static function upload($params, $cb = uploadSignature) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.upload");

        //Получить дкумент по переданому id
        $doc = TDataBaseDocument::getDocumentById($params['id']);
        $accessToken = null;
        try {
            $accessToken = TAuthCommand::getAccessTokenByRefreshToken($params['token']);
            $accessToken = $accessToken["access_token"];
        } catch (OAuth2Exception $ex) {
            $res["message"] = $ex->message;
            AjaxSign::sendSetStatus($params["operationId"], SIGN_STATUS_CANCELED);
            return $res;
        }
        if (beforeUploadSignature($doc, $accessToken) !== false) {
            if ($doc) {
                $newDoc = $doc->copy();
                $signers = urldecode($params["signers"]);
                $newDoc->setSigners($signers);
                $newDoc->setType(DOCUMENT_TYPE_SIGNATURE);
                $newDoc->setParent($doc);
                $signature = $_FILES["signature"];
                if ($cb) {
                    $cb($newDoc, $signature, $params['extra']);
                }
                $newDoc->save();
                $doc->getStatus()->setValue(DOCUMENT_STATUS_DONE);
                $doc->getStatus()->save();
                AjaxSign::sendSetStatus($params["operationId"]);
                $res["success"] = true;
                $res["message"] = "File uploaded";
            } else {
                $res["message"] = "Document is not found";
            }
        } else {
            $res["message"] = "Canceled in beforeUploadSignature function";
        }
        return $res;
    }

    static function status($params) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.status");
        $id = $params["id"];
        $doc = TDataBaseDocument::getDocumentById($id);
        if (!$doc) {
            header("HTTP/1.1 500 Internal Server Error");
            $res["message"] = "Document is not found";
            echo json_encode($res);
            die();
        }
        $res['success'] = true;
        $doc = $doc->getLastDocument();
        if ($doc->getStatus() && $doc->getStatus()->getValue()) {
            $res['message'] = $doc->getStatus()->getValue();
        } else {
            $res['message'] = DOCUMENT_STATUS_DONE;
        }
        return $res;
    }

    static function view($params, $cb = viewSignature) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.view");

        //Получить дкумент по переданому id
        $doc = TDataBaseDocument::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            $ajaxParams = AjaxParams::fromArray($params);
            $cb($last, $ajaxParams);
            $res = json_decode(AjaxSign::sendViewRequest($last, $ajaxParams));
        } else {
            $res["message"] = "Document is not found";
        }
        return $res;
    }

    static function content($params, $cb = getContent) {
        $res = array("success" => false, "message" => "Unknown error in Ajax.content");

        $doc = TDataBaseDocument::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            getContent($last, $params['token']);
            $file = urldecode($last->getPath());
            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $last->getName());
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
            }
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            $res["message"] = "Document is not found";
            echo json_encode($res);
            die();
        }

        die();
    }

    static function token($params) {
        $res = array("success" => true, "message" => "");
        try {
            $token = OAuth2::getFromSession();
            //$refreshToken = $token->getRefreshToken();
            //$token->refresh();
            $accessToken = $token->getAccessToken();
            $res["message"] = $accessToken;
        } catch (OAuth2Exception $ex) {
            header("HTTP/1.1 500 Internal Server Error");
            $res["message"] = $ex->message;
            echo json_encode($res);
            die();
        }
        return $res;
    }

}
