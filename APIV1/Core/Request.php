<?php

class Request {
    public array $params = [];
    public string $ipadr = "";
    public string|bool $auth = false;

    public function __construct() {
        

        /* Проверяем Get параметры */
        if (!empty($_GET)) {
            $this->params = array_merge($this->params, $_GET);
        }

        /* Проверяем POST параметры */
        if (!empty($_POST)) {
            $this->params = array_merge($this->params, $_POST);
        }

        /* Проверяем массив файлов, переданных через POST */
        if (!empty($_FILES)) {
            $this->params = array_merge($this->params, $_FILES);
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