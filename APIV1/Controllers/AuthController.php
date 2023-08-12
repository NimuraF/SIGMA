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
                    $token = Token::setNewToken("users_tokens", $user[0]['id']);

                    /* Генерируем и устанавливаем рефреш-токен */
                    $refreshToken = Token::setNewToken("users_refresh_tokens", $user[0]['id']);


                    /* Если удалось добавить все токены в БД, то устанавливаем куку и её время жизни (24 часа) */
                    if($token && $refreshToken) 
                    {

                        /* Устанавливаем токен сессии */
                        setcookie("token", $token, [
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

                        /* Удаляем пароль из возвращаемой информации */
                        unset($user[0]['password']);

                        return $this->json(['token' => $token, 'user' => $user]);

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
    public function getAuthorizedUser(Request $request) {

        if ($request->auth) {

            $DB = new DB();

            $currentUserID = $DB->select('users_tokens', ['user_id'])->where([['token', '=', $request->auth]])->get()[0]['user_id'];

            if ($currentUserInfo = $DB->select('users', ['id', 'name', 'avatar', 'banner'])->where([['id', '=', $currentUserID]])->get()) {
                $roles = $DB->select('roles_users', ['role_name'])->where([['user_id', '=', $currentUserID]])->get();
                return $this->json(['user' => $currentUserInfo, 'roles' => $roles]);
            }
        }

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

                /* Получаем данные созданного пользователя */
                $user = $DB->select('users', ['id', 'name', 'avatar', 'banner', 'sex'])->where([
                    ['email', '=', $email],
                    ['name', '=', $name]
                ])->get();

                if ($DB->insert("roles_users", ['user_id' => $user[0]['id'], 'role_name' => 'User'])) {

                    $token = Token::setNewToken('users_tokens', $user[0]['id']);
                    $refreshToken = Token::setNewToken('users_refresh_tokens', $user[0]['id']);

                    /* Если удалось создать и записать токен пользователя, а так же resfresh-токен, то устанавливаем его сразу же в куки */
                    if ($token && $refreshToken) {
                        /* Устанавливаем токен сессии */
                        setcookie("token", $token, [
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

                        return $this->json(['token' => $token, 'user' => $user]);
                    }
                    return Controller::errorMessage("Something went worng while set tokens...");
                }
                return Controller::errorMessage("Something went wrong while set default role!");
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

        return $this->json();
        
    }
}