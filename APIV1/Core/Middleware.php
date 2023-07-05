<?php

class Middleware {

    /* Метод, отвечающий за отправку сообщения и прерывание при неуспешном выполнении */
    public function error(string $message) {

        /* СОздаё мобъект анонимного класса и упаковываем его в json */
        echo json_encode(new class ($message) {
            public $access = false;
            public $errorm = "";

            public function __construct(string $message) {
                $this->errorm = $message;
            }
        });

        exit;
    }

}