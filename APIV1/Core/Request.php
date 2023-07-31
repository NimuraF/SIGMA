<?php

final class Request {

    public array $options = []; //Параметр для опций маршрута (Controller, Action, Method, Route)

    public array $params = []; //Параметр для GET/POST/FILES параметров, передаваемых, вместе с маршрутом

    public array $cookies = []; //Параметр для кук, пришедших вместе с запросом

    public string $currentPath = ""; //Параметр, отвечающий за полный путь текущего маршрута (без GET-парамтеров)

    public string $currentMeth = ""; //Параметр, отвечающий за метод текущего маршрута (дублирует информацию из options, но доступен раньше)

    public string $ipadr = ""; // IP-адрес текущего клиента

    public string|bool $auth = false; // Наличие авторизации у запроса, пришедшего с клиента (токен/false)

    public int $user_id = -1; //ID текущего пользователя при наличии авторизации



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

        /* Проверяем массив КУК */
        if (!empty($_COOKIE)) {
            $this->cookies = array_merge($this->cookies, $_COOKIE);
        }
    }



    /* Функция, возвращающая true, если параметры присутствуют и false, если массив пуст */
    public function checkParams() : bool {
        if (!empty($this->params)) {
            return true;
        }
        return false;
    }



    /* Установка параметров маршрута */
    public function setRouteOptions(array $options) : void {
        $this->options = $options;
    }

    /* Установка текущего пути маршрута */
    public function setCurrentPath(string $path) : void {
        $this->currentPath = $path;
    }

    /* Установка текущего метода маршрута */
    public function setCurrentMethod(string $method) : void {
        $this->currentMeth = $method;
    }

    
}