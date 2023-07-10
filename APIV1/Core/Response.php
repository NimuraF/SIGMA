<?php

final class Response {

    /* Тело ответа */
    public $data = "";

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

}