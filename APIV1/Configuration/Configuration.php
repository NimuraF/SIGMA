<?php

namespace Configuration;

class Configuration {

    /* Массив для регистрации middleware-ов для конкретных маршрутов */
    static array $routeMiddlewares = [
        'auth' => './APIV1/Middlewares/Authorization/auth.php',
        'notauth' => './APIV1/Middlewares/Authorization/notauth.php',
        'permissioncheck' => './APIV1/Middlewares/Authorization/permissioncheck.php',
        'csrf' => './APIV1/Middlewares/CSRF/csrf.php'
    ];

    static array $globalMiddlewares = [
        'ipcheck' => './APIV1/Middlewares/ipcheck.php'
    ];

}