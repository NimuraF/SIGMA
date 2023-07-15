<?php

class notauthMiddleware extends Middleware {

    /*
        Метод, гарантирующий, что пользователь не будет
        уже авторизован
    */

    public function handle(Request $request, callable $next) : Response {

        $token = new Token();

        /* Проверяем токен */
        if ($token->getValidated()) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "already authorized";
            });
        }
        

        return $next($request);
    }

}