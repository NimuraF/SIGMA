<?php

class Request {
    private array $params = [];

    public function __construct() {

        /* Проверяем Get параметры */
        if (!empty($_GET)) {
            array_push(self::$params, $_GET);
        }

        /* Проверяем POST параметры */
        if (!empty($_POST)) {
            array_push(self::$params, $_POST);
        }
    }
}