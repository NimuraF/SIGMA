<?php

class ipcheckMiddleware extends Middleware {

    /* Данный middleware проверяет и определяет ip-адрес клиента */
    public function __construct() {  
        $ip = $_SERVER['REMOTE_ADDR'];
        Request::$ipadr = $ip;
    }

}