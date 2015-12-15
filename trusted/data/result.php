<?php

class TDBResult {

    /**
     *
     * @var mysqli_result
     */
    protected $data = null;

    function __construct($result) {
        $this->data = $result;
    }

    /**
     * Делает выборку значений полей в массив.
     */
    function Fetch() {
        $res = $this->data->fetch_assoc();
        debug("TDBResult::Fetch:", $res);
        return $res;
    }

}
