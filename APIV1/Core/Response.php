<?php

final class Response {

    /* Тело ответа */
    public $data = "";
    
    /* Параметр, отвечающий за наличие ошибок в middleware */
    public $errorm = [];

    public function __construct(object $myobject = null)
    {
        if ($myobject != null) {
            $this->data = json_encode($myobject);
        }
    }

    /* Метод для установки данных в объект Response */
    public function setData(?string $jsonData) {
        $this->data = $jsonData;
    }


    /* Функция для установки ошибки */
    public function setError(string $errom) : void {
        array_push($this->errorm, $errom);
    }

}