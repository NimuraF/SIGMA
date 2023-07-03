<?php

class Router {

    static array $routes = [];

    /* Метод иницализации маршрутизатора */
    static function Start() {

        $currentMethod = $_SERVER['REQUEST_METHOD'];
        $currentRoute = explode('?', $_SERVER['REQUEST_URI']);
        $currentRoute = $currentRoute[0];

        foreach (self::$routes as $route) {
            if ($route['method'] == $currentMethod && preg_match("/".$route['route']."/", $currentRoute)) 
            {

                $path = './APIV1/Controllers/'.$route['controller'].'.php';

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

    /* Метод, отвечающий за применение middlewar-ов к маршрутам */
    static function middleware() {

    }

    /* Метод редиректа */
    static function retirect() {

    }

}