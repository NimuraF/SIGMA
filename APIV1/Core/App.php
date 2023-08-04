<?php

final class App {


    public function handle(Request $request) : Response {

        /* Подключаем контроллер, отвечающий за обработку этого маршрута */
        $path = './APIV1/Controllers/'.$request->options['controller'].'.php';


        /* Проверяем существование файла, отвечающего за данный контроллер */
        if(!file_exists($path)) {
            Router::error404();
        }

        require_once $path;

    
        /* Определяем параметры, полученные непосредственно из маршрута */
        $params = self::parseParams($request->options['route'], $request->currentPath);


        $controller = new $request->options['controller'];
        $action = $request->options['action'];


        /* Проверяем существование метода в контроллере */
        if (!method_exists($controller, $action)) {
            Router::error404();
        }

        $response = new Response();

        /* Извлекаем аргументы функции, которые будут в неё передаваться и проверяем на соответствие */
        $actionReflection = new ReflectionMethod($controller, $action);
        $actionReflectionParameters = $actionReflection->getParameters();

        /* 
            Проверяем, требуется ли передавать в контроллер объект класса request,
            по умолчанию требуется, чтобы при указании обязательной передачи реквеста
            он шёл первым в порядке аргументов
        */
        if (count($actionReflectionParameters) > 0) {
            if ($actionReflectionParameters[0]->getType()->getName() === "Request") {
                array_shift($actionReflectionParameters);
                if (count($actionReflectionParameters) === count($params)) {
                    $response->setData($controller->$action($request, ...$params));
                } 
                else {
                    Router::errorParseRouteParams();
                }
            } else {
                if (count($actionReflectionParameters) === count($params)) {
                    $response->setData($controller->$action(...$params));
                } 
                else {
                    Router::errorParseRouteParams();
                }
            }
        } else {
            $response->setData($controller->$action());
        }

        //dsgdgsdg

        return $response;
    }

    

    /* 
        Метод парсинга параметров из маршрута (HOST) 
    */
    static function parseParams(string $defaultRoute, string $currentRoute) : array {

        /* Отсекаем последний слэш, если есть */
        if (substr($currentRoute, -1) === "/") 
        {
            $currentRoute = rtrim($currentRoute, '/');
        }

        $params = [];

        /* Парсим параметры из пути */ //str_replace('$', '', str_replace('^', '', $defaultRoute)))
        $defaultRouteArray = explode('/', stripcslashes(preg_replace("/(\^|\\$)/", "", $defaultRoute)));
        for ($i = 0; $i < sizeof($currentRouteArray = explode('/', $currentRoute)); $i++) {
            if($defaultRouteArray[$i] !== $currentRouteArray[$i]) {
                array_push($params, $currentRouteArray[$i]);
            }
        }

        return $params;
    }


}