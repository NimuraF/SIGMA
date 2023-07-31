<?php

class permissioncheckMiddleware extends Middleware {

    public function handle(Request $request, callable $next) : Response 
    {

        $DB = new DB();

        /* Извлекаем весь список ролей у пользователя */
        $roles = $DB->select('roles_users', ['role_name'])->where(
                [
                    [
                        /* Вызываем call_user_func, чтобы сразу получить результат, 
                        потому что стрелочные функции в пыхе работают как помойка
                        */

                        'user_id', '=', call_user_func(function () use ($request) : string {
                            $result2 = new DB(); 
                            $role = $result2->select('users_tokens', ['user_id'])->where([['token', '=', $request->auth]])->get()[0]['user_id'];
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
            $permissions = $DB->query("SELECT * FROM roles_permissions WHERE role_name = '$roleName'");

            /* 
                Перебираем роли и для каждой извлекаем список 
                доступных маршрутов (хранится в виде наименований методов в БД)
            */
            foreach ($permissions as $permission) {

                
                /* Сравниваем с полученным из БД методом */
                if($request->options['action'] === $permission['permission']) {
                    $accesGranted = true;
                    break 2;
                }

            }
        }

        /* Если не удалось найти прав на подобный маршрут - бросаем ошибку */
        if($accesGranted !== true) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "non anouth permissions";
            });
        }

        return $next($request);
    }

}