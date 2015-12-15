<?php

class TDataBaseResult {

    /**
     *
     * @var mysqli_result
     */
    protected $data = null;

    function __construct($result) {
        $this->data = $result;
    }

    /**
     * Представляет данные в виде ассоциативного массива
     */
    function Fetch() {
        $res = $this->data->fetch_assoc();
        debug("TDataBaseResult::Fetch:", $res);
        return $res;
    }

}
