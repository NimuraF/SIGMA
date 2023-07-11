<?php

class permissioncheckMiddleware extends Middleware {

    public function handle(Request $request, callable $next) : Response 
    {

        $token = new Token();

        $DB = new DB();

        /* Извлекаем весь список ролей у пользователя */
        $roles = $DB->select('roles_users', ['role_name'])->where(
                [
                    [
                        /* Вызываем call_user_func, чтобы сразу получить результат, 
                        потому что стрелочные функции в пыхе работают как параша
                        */

                        'user_id', '=', call_user_func(function () use ($token) : string {
                            $result2 = new DB(); 
                            $role = $result2->select('tokens', ['user_id'])->where([['token', '=', $token->getToken()]])->get()[0]['user_id'];
                            return $role;
                        })
                    ]
                ]
            )->get();

        $accesGranted = false;

        /* Перебираем роли и смотрим доступ */
        foreach($roles as $role) {

            /* Определяем полное имя Роли */
            $roleName = $role['role_name'];

            /* Получаем список всех разрешений для текущей роли */
            $permissions = $DB->query("SELECT * FROM permissions WHERE role_name = '$roleName'");

            /* 
            Перебираем роли и для каждой извлекаем список доступных маршрутов (хранится в виде руглярок в БД),
            потому что было в падлу подключать Redis или заморачиваться, так как никто не оценит 
            */
            foreach ($permissions as $permission) {
                
                /* Сравниваем с полученной из БД регуляркой */
                if(preg_match("/".$permission['permission']."/", Router::$currentRoute) && $permission['method'] == Router::$currentMethod) {
                    $accesGranted = true;
                    break 2;
                }

            }
        }

        /* Если не удалось найти прав на подобный маршрут - бросаем ошибку */
        if($accesGranted !== true) {
            return new Response(new class {
                public string $access = "denied";
                public string $errorm = "non anouth permissions";
            });
        }

        return $next($request);
    }

}