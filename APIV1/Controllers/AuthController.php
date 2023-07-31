<?php

class AuthController extends Controller {

    /* Метод аутентификации пользователя */
    public function authentication (Request $request) {

        /* Проверяем, чтобы в реквесте находились параметры авторизации */
        if (isset($request->params['email']) && isset($request->params['password'])) {

            $DB = new DB();

            /* Извлекаем данные юзера по заданным параметрам */
            $user = $DB->select("users")->where(
                [
                    ['email', '=', $request->params['email']]
                ])
                ->get();

            /* Если пользователь был найден и при этом только один */
            if (sizeof($user) == 1) {

                /* Если пользователь был найден, то проверяем соответствие хэшей паролей */
                if (password_verify($request->params['password'], $user[0]['password'])) {

                    /* Генерируем и устанавливаем токен доступа */
                    $hash = Token::setNewToken("users_tokens", $user[0]['id']);

                    /* Генерируем и устанавливаем рефреш-токен */
                    $refreshToken = Token::setNewToken("users_refresh_tokens", $user[0]['id']);

                    /* Генерируем csrf-токен для текущего пользователя */
                    $csrf = Token::setNewToken("users_csrf_tokens", $user[0]['id']);

                    /* Если удалось добавить все токены в БД, то устанавливаем куку и её время жизни (24 часа) */
                    if($hash && $refreshToken && $csrf) 
                    {

                        /* Устанавливаем токен сессии */
                        setcookie("token", $hash, [
                            'expires' => time() + 60*60*24,
                            'path' => '/',
                            'domain' => 'gamedata.ru',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);

                        /* Устанавливаем рефреш токен */
                        setcookie("refresh-token", $refreshToken, [
                            'expires' => time() + 60*60*24*30,
                            'path' => '/',
                            'domain' => 'gamedata.ru',
                            'httponly' => true,
                        ]);

                        /* Устанавливаем csrf-токен для текущей сессии */
                        setcookie("csrf-token", $csrf, [
                            'expires' => time() + 60*60*24,
                            'path' => '/',
                            'domain' => 'gamedata.ru'
                        ]);

                        /* Удаляем пароль из возвращаемой информации */
                        unset($user[0]['password']);

                        return $this->json(['token' => $hash, 'user' => $user]);

                    } 
                    return Controller::errorMessage("Ooop's, something went wrong! Please try again later.");
                } 
                return Controller::errorMessage("Incorrect login or password.");
            } 
            return Controller::errorMessage("Incorrect login or password.");
        } 
        return Controller::errorMessage("Ooop's, something went wrong! Please try again later.");
    }


    /* Проверяем токен на валидность, если удалось попасть в этот метод, то возвращаем true */
    public function getAuthorizedUser() {

        $DB = new DB();

        //$currentUserID = $DB->select('tokens', ['user_id'])->where([['token', '=', $_COOKIE['token']]])->get()[0]['user_id'];

        // if ($currentUserInfo = $DB->select('users')->where([['id', '=', $currentUserID]])->get()) {
        //     return $this->json($currentUserInfo);
        // }

        return Controller::errorMessage('non authorized!');
    }


    /* Метод создания нового пользователя */
    public function createUser(Request $request) {

        /* Проверяем существование переданных почты, имени и пароля в реквесте */
        if (isset($request->params['email']) && isset($request->params['name']) && isset($request->params['password'])) {

            /* Извлекаем почту, имя и пароль */ 
            $email = $request->params['email'];
            $name = $request->params['name'];
            $password = $request->params['password'];

            /* Если переменные были переданы, то окрываем соединение с базой данных и загружаем данные */
            $DB = new DB();

            /* Если загрузка данных прошла успешно, то генерируем для пользователя стартовый токен */
            if($DB->insert('users', ['email' => $email, 'name' => $name, 'password' => password_hash($password, PASSWORD_DEFAULT)])) {

                /* Инициализируем класс токена */
                $token = new Token();

                /* Получаем id созданного пользователя */
                $userID = $DB->select('users', ['id'])->where([
                    ['email', '=', $email],
                    ['name', '=', $name]
                ])->get()[0]['id'];


                /* Если удалось создать и записать токен пользователя, то устанавливаем его сразу же в куки */
                if ($DB->insert('users_tokens', ['user_id' => $userID, 'token' => $hash = $token->createToken([$email, $name])])) {

                    /* По умолчанию при регистрации ставим роль user */
                    if ($DB->insert('roles_users', ['user_id' => $userID, 'role_name' => 'User'])) {

                        /* Время жизни куки ставим в 24 часа */
                        setcookie("token", $hash, [
                            'expires' => time() + 60*60*24,
                            'path' => '/',
                            'domain' => 'gamedata.ru',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);

                        return $this->json(['token' => $hash, 'id' => 'ddd']);

                    } 
                    return Controller::errorMessage("Ooop's, something went wrong! Failed to add role.");
                } 
                return Controller::errorMessage("Failed to generate token");
            } 
            return Controller::errorMessage("Ooop's, something went wrong!");
        } 
        return Controller::errorMessage("Incorrect params");
    }


    /* Метод для разлогинивая авторизованного пользователя */
    public function logout() {

        /* Убираем токен аутентификации */
        setcookie("token", "", [
            'expires' => -1,
            'path' => '/',
            'domain' => 'gamedata.ru',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        /* Убираем рефреш токен */
        setcookie("refresh-token", "", [
            'expires' => -1,
            'path' => '/',
            'domain' => 'gamedata.ru',
            'httponly' => true,
        ]);

        /* Убираем csrf-токен */
        setcookie("csrf-token", "", [
            'expires' => -1,
            'path' => '/',
            'domain' => 'gamedata.ru'
        ]);

        return $this->json();
        
    }
}