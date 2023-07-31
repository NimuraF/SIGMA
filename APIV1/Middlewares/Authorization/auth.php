<?php

class authMiddleware extends Middleware {
    
    /* 
        Класс authMiddleware отвечает за валидацию токена
        и проверку наличия аутентификации пользователя как
        таковой перед разрешением доступа к маршруту
    */
    public function handle(Request $request, callable $next) : Response {

        /* Записываем текущий токен, если он существует, или же NULL */
        $currentToken = isset($request->cookies['token']) ? $request->cookies['token'] : NULL;

        /* Записываем текущий refresh-токен, если он существует, или же NULL */
        $currentRefreshToken = isset($request->cookies['refresh-token']) ? $request->cookies['refresh-token'] : NULL;

        /* Проверяем токен доступа */
        if (!$currentUser = Token::validateToken($currentToken, "users_tokens")) {

            /* Проверяем refresh-токен */
            if (!$currentUser = Token::validateToken($currentRefreshToken, "users_refresh_tokens")) {

                return new Response(new class {
                    public bool $access = false;
                    public string $errorm = "invalid token";
                });

            } 

            /* Устанавливаем токен в БД, если не получилось - бросаем ошибку */
            if( !$newToken = Token::setNewToken("users_tokens", $currentUser) ) {
                return new Response(new class {
                    public bool $access = false;
                    public string $errorm = "Something went wrong during authorization!";
                });
            } 

            /* Записываем в текущий токен новый сгенерированный токен */
            $currentToken = $newToken; 

            /* Устанавливаем новый токен сессии */
            setcookie("token", $newToken, [
                'expires' => time() + 60*60*24,
                'path' => '/',
                'domain' => 'gamedata.ru',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

        }

        /* Устанавливаем токен в параметр auth-реквеста */
        $request->auth = $currentToken;

        /* Устанавливаем id текущего пользователя в реквест */
        $request->user_id = $currentUser;

        /* Устанавливаем последний ip-адрес данному пользователю */
        $DB = new DB();
        $DB->query("UPDATE users SET last_ip = INET_ATON('$request->ipadr') WHERE id = $request->user_id");

        return $next($request);
    }

}