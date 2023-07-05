<?php

class AuthController extends Controller {

    /* Метод аутентификации пользователя */
    public function authentication (Request $request) : bool {

        /* Определяем класс токена */
        $token = new Token();

        /* Если токена не существует */
        if (!$token->getToken()) { 

            /* Проверяем, чтобы в реквесте находились параметры авторизации */
            if (isset($request->params[0]['email']) && isset($request->params[0]['password'])) {

                $DB = new DB();

                /* Извлекаем данные юзера по заданным параметрам */
                $user = $DB->select("users")->where(
                    [
                        ['email', '=', $request->params[0]['email']]
                    ])
                    ->get();

                /* Если пользователь был найден, то проверяем соответствие хэшей паролей */
                if (sizeof($user) == 1) {

                    if (password_verify($request->params[0]['password'], $user[0]['password'])) {

                        /* Генерируем хэш-ключ */
                        $hash = hash('sha256', $request->params[0]['email'].$request->params[0]['email'].microtime());

                        /* Если удалось добавить хэш в БД, то устанавливаем куку и её время жизни (24 часа) */
                        if($DB->insert('tokens', ['user_id' => $user[0]['id'],'token' => $hash])) {
                            setcookie("token", $hash, time() + 60*60*24);
                            return true;
                        } 
                        else 
                        {
                            throw new Exception("Не удалось установить токен!");
                            return false;
                        }

                    } 
                    else 
                    {
                        throw new Exception("Неверный логин/пароль!");
                        return false;
                    }

                } 
                else 
                {
                    throw new Exception("Неверный логин/пароль!");
                    return false;
                }
            } 
            else {
                throw new Exception("Некорректно переданы параметры!");
                return false;
            }
        } 
        else 
        {
            return true;
        }

    }


    /* Метод создания нового пользователя */
    public function createUser(Request $request) : bool {

        /* Проверяем существование переданных почты, имени и пароля в реквесте */
        if (isset($request->params[0]['email']) && isset($request->params[0]['name']) && isset($request->params[0]['password'])) {

            /* Извлекаем почту, имя и пароль */
            $email = $request->params[0]['email'];
            $name = $request->params[0]['name'];
            $password = $request->params[0]['password'];

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


                /* Если удалось создать и записать токен пользователя, то устанавливаем его сразу же в куки*/
                if ($DB->insert('tokens', ['user_id' => $userID, 'token' => $userToken = $token->createToken([$email, $name])])) {

                    if ($DB->insert('roles_users', ['user_id' => $userID, 'role_name' => 'User'])) {

                        /* Время жизни куки ставим в 24 часа */
                        setcookie('token', $userToken, time() + 60*60*24);
                        return true;

                    } 
                    else 
                    {
                        throw new Exception("Упс, не удалось присвоить роль!");
                        return false;
                    }

                } 
                else 
                {
                    throw new Exception("Не удалось сгенерировать токен доступа");
                    return false;
                }

            } 
            else 
            {
                throw new Exception("Упс, что-то пошло не так");
                return false;
            }
        } 
        else 
        {
            throw new Exception("Некорректные параметры");
            return false;
        }
    }

}