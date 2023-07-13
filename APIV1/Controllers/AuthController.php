<?php

class AuthController extends Controller {

    /* Метод аутентификации пользователя */
    public function authentication (Request $request) : bool {

        /* Определяем класс токена */
        $token = new Token();

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

                    /* Генерируем хэш-ключ */
                    $hash = hash('sha256', $request->params['email'].$request->params['email'].microtime());

                    /* Если удалось добавить хэш в БД, то устанавливаем куку и её время жизни (24 часа) */
                    if($DB->insert('tokens', ['user_id' => $user[0]['id'],'token' => $hash])) {
                        setcookie("token", $hash, time() + 60*60*24);
                        return true;
                    } 
                    else 
                    {
                        return Controller::errorMessage("Ooop's, something went wrong! Please try again later.");
                    }

                } 
                else 
                {
                    return Controller::errorMessage("Incorrect login or password.");
                }
            } 
            else 
            {
                return Controller::errorMessage("Incorrect login or password.");
            }
        } 
        else 
        {
            return Controller::errorMessage("Ooop's, something went wrong! Please try again later.");
        }
    }


    /* Метод создания нового пользователя */
    public function createUser(Request $request) : bool {

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
                if ($DB->insert('tokens', ['user_id' => $userID, 'token' => $userToken = $token->createToken([$email, $name])])) {

                    /* По умолчванию при регистрации ставим роль user */
                    if ($DB->insert('roles_users', ['user_id' => $userID, 'role_name' => 'User'])) {

                        /* Время жизни куки ставим в 24 часа */
                        setcookie('token', $userToken, time() + 60*60*24);
                        return true;

                    } 
                    else 
                    {
                        return Controller::errorMessage("Ooop's, something went wrong! Failed to add role.");
                    }

                } 
                else 
                {
                    return Controller::errorMessage("Failed to generete token");
                }

            } 
            else 
            {
                return Controller::errorMessage("Ooop's, something went wrong!");
            }
        } 
        else 
        {
            return Controller::errorMessage("Incorrect params");
        }
    }

}