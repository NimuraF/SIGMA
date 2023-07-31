<?php

class notauthMiddleware extends Middleware {

    /*
        Метод, гарантирующий, что пользователь не будет
        уже авторизован
    */

    public function handle(Request $request, callable $next) : Response {

        /* Проверяем токен доступа */
        if ( $token = Token::validateToken(isset($request->cookies['token']) ? $request->cookies['token'] : NULL, "users_tokens") ) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "already authorized";
            });
        }

        return $next($request);
    }

}