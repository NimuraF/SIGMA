<?php

class authMiddleware extends Middleware {
    
    /* 
    Класс authMiddleware отвечает за валидацию токена
    и проверку наличия аутентификации пользователя как
    таковой перед разрешением доступа к маршруту
    */

    public function __construct() {
        $token = new Token();

        /* Проверяем токен */
        if (!$token->getValidated()) {
            echo json_encode(new class {
                public $access = false;
                public $errorm = "invalid token";
            });
            exit;
        }
    }

}