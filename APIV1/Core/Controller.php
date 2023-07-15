<?php


abstract class Controller {
    
    /* Метод преобразования и отправки информации в json-формате */
    public function json(array $toJson = []) {
        /* Так же добавляем параметр о том, что доступ получен */
        $json = json_encode(['access' => true, 'data' => $toJson]);
        return $json;
    }

    /* Метод отправки ошибки при каком-то условии */
    static function errorMessage(string $errorMessage) {
        $error = json_encode(['access' => false, 'errorm' => $errorMessage]);
        return $error;
    }
}