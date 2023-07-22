<?php

use Configuration\Configuration;

final class Router {

    static array $routes = []; //Таблица локальных маршрутов

    static array $middlewares = []; //Таблица middleware'ов для текущего маршрута
    static array $lmiddlewares = []; //Таблица всех локальных middleware'ов


    static string $currentRoute = ""; //Во время заполнения выступает буфером, а при работе хранит текущий маршрут
    static string $currentMethod = ""; //Во время заполнения выступает буфером, а при работе хранит текущий маршрут


    /* 
        Определяем action контроллера, 
        привязанный к текущему маршруту, 
        нужен для корректной работы middleware permissioncheck 
    */
    static string $currentAction = "";

    
    
    /* Метод иницализации маршрутизатора */
    static function Start() {

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

        /* Перебираем таблицу маршрутов и ищем совпадения */
        foreach (self::$routes as $route) 
        {
            if ($route['method'] == self::$currentMethod && preg_match("/".$route['route']."/", self::$currentRoute)) 
            {

                /* Устанавливаем текущий action */
                self::$currentAction = $route['action'];

                /* Добавляем локальные middlewares */
                self::addLocalMiddlewares($route['route'], $route['method']);
                
                /* Определяем reponse после вызова всех middleware */
                if (($response = self::parseMiddlewares($request))->data === "") {


                    /* Подключаем контроллер, отвечающий за обработку этого маршрута */
                    $path = './APIV1/Controllers/'.$route['controller'].'.php';

                    /* Проверяем существование файла, отвечающего за данный контроллер */
                    if(!file_exists($path)) {
                        Router::error404();
                    }

                    require_once $path;

                    /* Определяем параметры, полученные непосредственно из маршрута */
                    $params = self::parseParams($route['route'], self::$currentRoute);

                    $controller = new $route['controller'];
                    $action = $route['action'];


                    /* Проверяем существование метода в контроллере */
                    if (!method_exists($controller, $action)) {
                        Router::error404();
                    }

                    if ($request->checkParams() || $route['method'] == "POST") {
                        $response->setData($controller->$action($request, ...$params));
                    } else {
                        $response->setData($controller->$action(...$params));
                    }
                    
                    self::handle($response);

                    return 1;
                } 
                else 
                {
                    self::handle($response);
                    return 0;
                }
            }
        }

        /* Если не удалось найти совпадения, то бросаем 404 ошибку */
        Router::error404();

        return 1;
    }


    /* Метод для заполнения таблицы маршрутов (GET) */
    static function get(string $route, string $controller, string $action) {

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
    static function post(string $route, string $controller, string $action) {

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


    /* Метод парсинга параметров из маршрута */
    static function parseParams(string $defaultRoute, string $currentRoute) : array {

        /* Отсекаем последний слэш, если есть */
        if (substr($currentRoute, -1) === "/") 
        {
            $currentRoute = rtrim($currentRoute, '/');
        }

        $params = [];

        /* Парсим параметры из пути */ //str_replace('$', '', str_replace('^', '', $defaultRoute)))
        $defaultRouteArray = explode('/', stripcslashes(preg_replace("/\w*(\^|\\$)\w*/", "", $defaultRoute)));
        for ($i = 0; $i < sizeof($currentRouteArray = explode('/', $currentRoute)); $i++) {
            if($defaultRouteArray[$i] !== $currentRouteArray[$i]) {
                array_push($params, $currentRouteArray[$i]);
            }
        }

        return $params;
    }

    /* Метод, отвечающий за прикрепление middlewar-ов к маршрутам */
    static function middleware(string $middleware) : void {

        /* Разбиваем переданные middleware */
        $arrMiddlewares = explode('|', $middleware);

        /* Регистрируем middleware для конкретного маршрута в таблице middleware-ов */
        foreach($arrMiddlewares as $currentLocalMiddleware) {
            if (array_key_exists($currentLocalMiddleware, Configuration::$routeMiddlewares) && $currentLocalMiddleware !== "") {
                array_push(self::$lmiddlewares, ['method' => self::$currentMethod, 'route' => self::$currentRoute, 'middlewareName' => $currentLocalMiddleware]);
            }
        }
    }


    /* Метод, отвечающий за применение глобальных маршрутов */
    static function parseMiddlewares(Request $request) : Response {

        /* Достаём первый middleware из маршрута */
        $currentMiddlewareName = key(self::$middlewares)."Middleware";
        $currentMiddlewarePath = array_shift(self::$middlewares);

        if ($currentMiddlewarePath !== NULL) {

            /* Подключаем файл с текущим middleware */
            require_once $currentMiddlewarePath; 

            /* Создаём класс middleware */
            $middleware = new $currentMiddlewareName;

            /* Рекурсивно вызываем middleware по очереди */
            return $middleware->handle($request, [self::class, 'parseMiddlewares']);
        }

        return new Response;
    }

    /* Метод, отвечающий за добавление локальных middleware'ов к маршрутам */
    static function addLocalMiddlewares(string $route, string $method) : void {

        /* ПРикрепляем все middleware's, которые привязаны к этому маршруту */
        foreach(self::$lmiddlewares as $middleware) {
            if ($middleware['method'] == $method && $middleware['route'] == $route) {
                self::$middlewares[$middleware['middlewareName']] = Configuration::$routeMiddlewares[$middleware['middlewareName']];
            }
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

    /* Метод редиректа */
    static function redirect() {

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
    

}