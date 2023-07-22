<?php

class authMiddleware extends Middleware {
    
    /* 
    Класс authMiddleware отвечает за валидацию токена
    и проверку наличия аутентификации пользователя как
    таковой перед разрешением доступа к маршруту
    */

    public function handle(Request $request, callable $next) : Response {

        $token = new Token();

        /* Проверяем токен */
        if (!$token->getValidated()) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "invalid token";
            });
        } 
        else 
        {
            /* Устанавливаем токен в объект реквеста */
            $request->auth = $token->getToken();
            
            /* Получаем текущего юзера после валидации токена */
            $currentUser = $token->getUser();

            /* Устанавливаем последний ip-адрес данному пользователю */
            $DB = new DB();
            $DB->query("UPDATE users SET last_ip = INET_ATON('$request->ipadr') WHERE id = $currentUser");
        }

        return $next($request);
    }

}