<?php

namespace Configuration;

final class Configuration {

    /* Параметры соединения с базой данных */
    const DB_HOST = "localhost";
    const DB_PORT = "3306";
    const DB_NAME = "gamedata";
    const DB_USER = "root";
    const DB_PASS = "1111";

    /* Параметры загрузки изображений по умолчанию */
    const IMAGE_MAX_WIDTH_DEFAULT = 4000;
    const IMAGE_MAX_HEIGHT_DEFAULT = 2000;
    const IMAGE_MAX_SIZE = 10000000;

    /* Базовый путь для хранилища */
    const STORAGE_PATH = "./Storage/";


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