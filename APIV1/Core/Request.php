<?php

class Request {
    public array $params = [];
    static string $ipadr = "";

    public function __construct() {
        
        /* Проверяем Get параметры */
        if (!empty($_GET)) {
            array_push($this->params, $_GET);
        }

        /* Проверяем POST параметры */
        if (!empty($_POST)) {
            array_push($this->params, $_POST);
        }
        
    }

    /* Функция, возвращающая true, если параметры присутствуют и false, если массив пуст */
    public function checkParams() : bool {
        if (!empty($this->params)) {
            return true;
        }
        return false;
    }

}