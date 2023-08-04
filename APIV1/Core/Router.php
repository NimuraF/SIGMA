<?php

use Configuration\Configuration;

final class Router {

    static array $routes = []; //Таблица локальных маршрутов

    static array $middlewares = []; //Таблица middleware'ов для текущего маршрута
    static array $lmiddlewares = []; //Таблица всех локальных middleware'ов

    static array $nonCSRF = []; //Таблиц POST-маршрутов, исключающих CSRF-защиту


    static string $currentRoute = ""; //Во время заполнения выступает буфером
    static string $currentMethod = ""; //Во время заполнения выступает буфером, а при работе хранит текущий маршрут

    
    /* Метод иницализации маршрутизатора */
    static function Start() : int {

        self::$middlewares = Configuration::$globalMiddlewares; //Загружаем таблицу глобальных middleware'ов

        self::$currentMethod = $_SERVER['REQUEST_METHOD']; //Устанавливаем текущее значение метода для работы middleware'ов

        $currentRoute = explode('?', $_SERVER['REQUEST_URI']); 
        self::$currentRoute = $currentRoute[0]; //Устанавливаем текущее значение пути для работы middleware'ов


        /* Проверяем, не является ли пришедший запрос json-ом */
        if (isset(getallheaders()['Content-Type'])) {
            if ( getallheaders()['Content-Type'] == 'application/json') {
                $_POST = json_decode(file_get_contents('php://input'), true);
            }
        }


        /* Инициализируем объект реквеста */
        $request = new Request;


        /* Устанавливаем полный путь текущего запроса в объект Request */
        $request->setCurrentPath(self::$currentRoute);

        /* Устанавливаем метод текущего запроса в объект Request */
        $request->setCurrentMethod(self::$currentMethod);


        /* Перебираем таблицу маршрутов и ищем совпадения */
        foreach (self::$routes as $route) 
        {
            if ($route['method'] == $request->currentMeth && preg_match("/".$route['route']."/", $request->currentPath)) 
            {

                /* Устанавливаем параметры маршрута в реквест */
                $request->setRouteOptions($route);

                /* Добавляем локальные middlewares */
                self::addLocalMiddlewares($route['route'], $route['method']);

                /* Вызываем обработчик приложения */
                $response = self::app($request);
                self::handle($response);
                return 1;

            }
        }

        /* Если не удалось найти совпадения, то бросаем 404 ошибку */
        Router::error404();
        return 0;
        
    }





    /* Метод для заполнения таблицы маршрутов (GET) */
    static function get(string $route, string $controller, string $action) : string {

        /* Добавляем новую запись в таблицу маршрутов */

        /* 
        Если в маршруте содержится параметр, т.е. значение типа {id},
        то его требуется преобразовать в регулярное выражение, для чего
        производим специальную операцию
        */
        if (preg_match("/{*?}/", $route)) { //Если встретилась конструкция {...}, т.е. параметр в маршруте

            $arrayRoute = explode('/', $route); //Разделяем весь маршрут на отдельные компоненты

            ["","games", "{id}"];

            foreach($arrayRoute as $key => $routeElement) { //Просматриваем все компоненты на предмет потребности в составлении регулярки

                if (preg_match("/{*?}/", $routeElement)) { //Если требуется составление регулярного выражения у компонента, то делаем это
                    $arrayRoute[$key] = "\w*";
                }
            }

            $route = "^".implode("\/", $arrayRoute)."$";
        } 
        else {
            $route = "^".str_replace('/', "\/", $route)."$";
        }

        /* Устанавливаем контекст текущего маршрута для последующего навешивания middleware-ов */
        self::$currentRoute = $route;
        self::$currentMethod = "GET";


        array_push(self::$routes, ['method' => 'GET', 'route' => $route, 'controller' => $controller, 'action' => $action]);
        return self::class;
    }

    /* Метод для заполнения таблицы маршрутов (POST) */
    static function post(string $route, string $controller, string $action) : string {

        /* Добавляем новую запись в таблицу маршрутов */

        /* 
        Если в маршруте содержится параметр, т.е. значение типа {id},
        то его требуется преобразовать в регулярное выражение, для чего
        производим специальную операцию
        */
        if (preg_match("/{*?}/", $route)) { //Если встретилась конструкция {...}, т.е. параметр в маршруте

            $arrayRoute = explode('/', $route); //Разделяем весь маршрут на отдельные компоненты

            ["","games", "{id}"];

            foreach($arrayRoute as $key => $routeElement) { //Просматриваем все компоненты на предмет потребности в составлении регулярки

                if (preg_match("/{*?}/", $routeElement)) { //Если требуется составление регулярного выражения у компонента, то делаем это
                    $arrayRoute[$key] = "\w*";
                }
            }

            $route = "^".implode("\/", $arrayRoute)."$";
        } 
        else {
            $route = "^".str_replace('/', "\/", $route)."$";
        }

        /* Устанавливаем контекст текущего маршрута для последующего навешивания middleare-ов */
        self::$currentRoute = $route;
        self::$currentMethod = "POST";

        array_push(self::$routes, ['method' => 'POST', 'route' => $route, 'controller' => $controller, 'action' => $action]);
        return self::class;
    }

    /* Метод, отвечающий за прикрепление middlewar-ов к маршрутам */
    static function middleware(string $middleware) : string {

        /* Разбиваем переданные middleware */
        $arrMiddlewares = explode('|', $middleware);

        /* Регистрируем middleware для конкретного маршрута в таблице middleware-ов */
        foreach($arrMiddlewares as $currentLocalMiddleware) {
            if (array_key_exists($currentLocalMiddleware, Configuration::$routeMiddlewares) && $currentLocalMiddleware !== "") {
                array_push(self::$lmiddlewares, ['method' => self::$currentMethod, 'route' => self::$currentRoute, 'middlewareName' => $currentLocalMiddleware]);
            }
        }

        return self::class;
    }

    /* Метод, отвечающий за исключение из текущего POST-маршрута csrf-защиты */
    static function excludeCSRF() : string {

        self::$nonCSRF[self::$currentRoute] = true;

        return self::class;
    }











    /* 
        Метод, отвечающий за исполнение middleware и
        передачу управления (если в цепи не возникло
        ошибок) в обработчик самого приложения
    */
    static function app(Request $request) : Response {

        /* Достаём первый middleware из маршрута */
        $currentMiddlewareName = key(self::$middlewares)."Middleware";
        $currentMiddlewarePath = array_shift(self::$middlewares);

        if ($currentMiddlewarePath !== NULL) {

            /* Подключаем файл с текущим middleware */
            require_once $currentMiddlewarePath; 

            /* Создаём класс middleware */
            $middleware = new $currentMiddlewareName;

            /* Рекурсивно вызываем middleware по очереди */
            return $middleware->handle($request, [self::class, 'app']);
        }

        /* Передаём управлением в обработчик приложения */
        $app = new App;
        return $app->handle($request);
    }








    /* Метод, отвечающий за добавление локальных middleware'ов к маршрутам */
    static function addLocalMiddlewares(string $route, string $method) : void {

        /* Прикрепляем все middleware's, которые привязаны к этому маршруту */
        foreach(self::$lmiddlewares as $middleware) {
            if ($middleware['method'] == $method && $middleware['route'] == $route) {
                self::$middlewares[$middleware['middlewareName']] = Configuration::$routeMiddlewares[$middleware['middlewareName']];
            }
        }

        /* Если метод POST, то добавляем CSRF-верификацию, за исключением форм авторизации и проверки пользователя */
        if ( $method == "POST" && !isset(self::$nonCSRF[$route]) ) {
            self::$middlewares["csrf"] = Configuration::$routeMiddlewares["csrf"];
        }

    }





    /* 
        Метод, отвечающий за отправку Response и
        установку точки завершения программмы, т.е.
        данный метод всегда является финальным при
        отправке ответа
    */
    static function handle(Response $response) : void {
        echo $response->data;
        exit();
    }





   


    /* Метод для отправки 404 ответа */
    static function error404 () : void {
        self::handle(new Response(new class {
            public bool $access = false;
            public string $errorm = "Invalid route!";
        }));
    } 

    /* Метод для отправки ответа при невозможности соединиться с БД */
    static function errorDB() : void {
        self::handle(new Response(new class {
            public bool $access = false;
            public string $errorm = "Failed to connect to DB!";
        }));
    }

    /* Метод для отправки ошибки при несоответствии параметров маршрута */
    static function errorParseRouteParams() : void {
        self::handle(new Response(new class {
            public bool $access = false;
            public string $errorm = "Error while parcing route params!";
        }));
    }
    

}