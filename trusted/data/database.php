<?php

class TDataBase {

    /**
     * База данных
     * @var mysqli 
     */
    protected $db = null;
    protected $error = null;

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * Выводит текст последней ошибки
     * @return string
     */
    public function LastError() {
        if ($this->db->error) {
            return $this->db->error;
        }
        return $this->error;
    }

    /**
     * Открывает соединение с базой данных. Метод возвращает "true" при успешном открытии соединения или "false" при ошибке.
     * @param string    $host       Сервер (хост) базы данных.
     * @param string    $db         Имя базы данных.
     * @param string    $login      Логин.
     * @param string    $password   Пароль.
     * @return boolean
     */
    function Connect($host, $db, $login, $password) {
        debug("TDataBase.connect: Connect to '$db' at '$host'");
        $this->db = new mysqli($host, $login, $password, $db);
        if ($this->db->connect_errno) {
            $this->error = $this->db->connect_error;
            return false;
        }
        $this->db->set_charset($password);
        return true;
    }

    /**
     * Закрывает соединение с базой данных.
     */
    function Disconnect() {
        $this->db->close();
    }
    
    function EscapeString($text){
        return $this->db->escape_string($text);
    }
    
    function LastID(){
        return $this->db->insert_id;
    }

    /**
     * Метод выполняет запрос к базе данных и если не произошло ошибки возвращает результат
     * @param string $sql
     * @param boolean $ignore_errors
     * @param string $error_position
     * @param array $Options
     */
    function Query($sql, $ignore_errors = false, $error_position = "", $Options = array()) {
        debug("TDataBase.Query: SQL", $sql);
        $res = new TDBResult($this->db->query($sql));
        debug("TDataBase::Query: TDBResult", $res);
        if ($this->db->errno){
            $message = "TDataBase.Query: Error".$this->LastError();
            debug($message);
            if (!$ignore_errors){
                throw new Exception($message, 0, NULL);
            }
        }
        return $res;
    }

}
