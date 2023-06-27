<?php

class Router {

    static array $routes = [];

    static function Start() {

        $method = $_SERVER['REQUEST_METHOD'];
        $currentRoute = $_SERVER['REQUEST_URI'];

        foreach (self::$routes as $route) {
            if ($route['method'] == $method && $route['route'] == $currentRoute) {

                $path = './APIV1/Controllers/'.$route['controller'].'.php';

                if(file_exists($path)) {
                    require_once $path;
                } else {
                    throw new Exception("Error 404");
                }

                $controller = new $route['controller'];
                $action = $route['action'];

                if (method_exists($controller, $action)) {
                    $controller->$action();
                }
                break;
            }
        }

    }

    static function get(string $route, string $controller, string $action) {
        /* Добавляем новую запись в таблицу маршрутов */
        array_push(self::$routes, ['method' => 'GET', 'route' => $route, 'controller' => $controller, 'action' => $action]);
        return self::class;
    }

    static function post() {

    }

}