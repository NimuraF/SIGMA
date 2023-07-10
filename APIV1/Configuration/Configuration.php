<?php

namespace Configuration;

class Configuration {

    /* Массив для регистрации middleware-ов для конкретных маршрутов */
    static array $routeMiddlewares = [
        'auth' => './APIV1/Middlewares/Authorization/auth.php',
        'permissioncheck' => './APIV1/Middlewares/Authorization/permissioncheck.php'
    ];

    static array $globalMiddlewares = [
        'ipcheck' => './APIV1/Middlewares/ipcheck.php'
    ];

}