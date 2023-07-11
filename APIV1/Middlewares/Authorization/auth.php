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
                public string $access = "denied";
                public string $errorm = "invalid token";
            });
        } 
        else 
        {
            $request->auth = $token->getToken();
        }

        return $next($request);
    }

}