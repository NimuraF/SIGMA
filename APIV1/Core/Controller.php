<?php


class Controller {
    
    public function showJson(array $toJson) {
        /* Так же добавляем параметр о том, что доступ получен */
        $json = json_encode(['access' => true, 'data' => $toJson, 'ip' => Request::$ipadr]);
        echo $json;
    }
}