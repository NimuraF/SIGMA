<?php

use Configuration\Configuration;

class Router {

    static array $routes = [];
    static array $middlewares = [];
    static string $currentRoute = ""; //Во время заполнения выступает итератором, а при работе хранить текущий маршрут
    static string $currentMethod = ""; //

    /* Метод иницализации маршрутизатора */
    static function Start() {

        $currentMethod = $_SERVER['REQUEST_METHOD'];
        $currentRoute = explode('?', $_SERVER['REQUEST_URI']);
        $currentRoute = $currentRoute[0];

        self::$currentRoute = $currentRoute; //Устанавливаем текущее значение пути для работы middleware'ов
        self::$currentMethod = $currentMethod; //Устанавливаем текущее значение метода для работы middleware'ов

        /* Парсим глобальный middleware's */
        self::parseGlobalMiddlewares();

        /* Перебираем таблицу маршрутов и ищем совпадения */
        foreach (self::$routes as $route) {
            if ($route['method'] == $currentMethod && preg_match("/".$route['route']."/", $currentRoute)) 
            {

                /* Вызываем все middleware's, которые привязаны к этому маршруту */
                foreach(self::$middlewares as $middleware) {
                    if ($middleware['method'] == $route['method'] && $middleware['route'] == $route['route']) {

                        /* Подключаем файл с middleware'ом, который хранится в классе конфигурации в константе routeMiddlewares */
                        require_once Configuration::$routeMiddlewares[$middleware['middlewareName']];

                        $middlewareName = $middleware['middlewareName']."Middleware";

                        $upMiddleware = new $middlewareName;

                    }
                }

                /* Подключаем контроллер, отвечающий за обработку этого маршрута*/
                $path = './APIV1/Controllers/'.$route['controller'].'.php';

                /* Проверяем существование файла, отвечающего за данный контроллер */
                if(file_exists($path)) {
                    require_once $path;
                } else {
                    throw new Exception("Error 404");
                }


                /* Инициализируем объект реквеста */
                $request = new Request;

                /* Определяем параметры, полученные непосредственно из маршрута */
                $params = self::parseParams($route['route'], $currentRoute);

                $controller = new $route['controller'];
                $action = $route['action'];


                /* Проверяем существование метода в контроллере */
                if (method_exists($controller, $action)) {
                    if ($request->checkParams()) {
                        $controller->$action($request, ...$params);
                    } else {
                        $controller->$action(...$params);
                    }
                }
                break;
            }
        }

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

            $route = implode("\/", $arrayRoute)."$";
        } 
        else {
            $route = str_replace('/', "\/", $route)."$";
        }

        /* Устанавливаем контекст текущего маршрута для последующего навешивания middleare-ов */
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

            $route = implode("\/", $arrayRoute)."$";
        } 
        else {
            $route = str_replace('/', "\/", $route)."$";
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

        /* Парсим параметры из пути */
        $defaultRouteArray = explode('/', stripcslashes(str_replace('$', '', $defaultRoute)));
        for ($i = 0; $i < sizeof($currentRouteArray = explode('/', $currentRoute)); $i++) {
            if($defaultRouteArray[$i] !== $currentRouteArray[$i]) {
                array_push($params, $currentRouteArray[$i]);
            }
        }

        return $params;
    }

    /* Метод, отвечающий за прикрепление middlewar-ов к маршрутам */
    static function middleware(string $middleware) {

        /* Разбиваем переданные middleware */
        $arrMiddlewares = explode('|', $middleware);

        /* Регистрируем middleware для конкретного маршрута в таблице middleware-ов */
        foreach($arrMiddlewares as $currentLocalMiddleware) {
            if (array_key_exists($currentLocalMiddleware, Configuration::$routeMiddlewares) && $currentLocalMiddleware !== "") {
                array_push(self::$middlewares, ['method' => self::$currentMethod, 'route' => self::$currentRoute, 'middlewareName' => $currentLocalMiddleware]);
            }
        }
    }


    /* Метод, отвечающий за применение глобальных маршрутов */
    static function parseGlobalMiddlewares() {

        /* Перебираем все глобальные middleware */
        foreach(Configuration::$globalMiddlewares as $key=>$globalM) {

            require_once $globalM;

            $globalMiddlewareName = $key."Middleware";

            $globalMiddleware = new $globalMiddlewareName();
        }

    }

    /* Метод редиректа */
    static function redirect() {

    }

}