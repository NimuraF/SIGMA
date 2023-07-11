<?php

require_once "Configuration/Configuration.php"; //Подключаем файл конфигурации

require_once "Core/Middleware.php";
require_once "Core/Router.php";
require_once "Core/Controller.php";
require_once "Core/Database.php";
require_once "Core/Request.php";
require_once "Core/Token.php";
require_once "Core/Response.php";
require_once "Core/Storage.php";


require_once "Filters/BaseFilter.php"; //Подключаем базовый фильтр

require_once "Routes/api.php"; //Подключаем таблицу маршрутов 

// try {
    Router::Start();
// } 
// catch (Exception $e) {
//     $e->getMessage();
// }
// catch (PDOException $e) {
//     $e->getMessage();
// }
