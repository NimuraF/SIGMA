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
        $params = Router::parseParams($request->options['route'], $request->currentPath);

        $controller = new $request->options['controller'];
        $action = $request->options['action'];


        /* Проверяем существование метода в контроллере */
        if (!method_exists($controller, $action)) {
            Router::error404();
        }

        $response = new Response();

        if ($request->checkParams() || $request->options['method'] == "POST") {
            $response->setData($controller->$action($request, ...$params));
        } else {
            $response->setData($controller->$action(...$params));
        }

        return $response;
    }

}