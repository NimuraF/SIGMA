<?php

/* Подключаем класс соединения с БД */
require_once "./APIV1/Core/Database.php";

/* Подрубаем композер */
require_once 'vendor/autoload.php';


require_once "BaseFabricator.php";
require_once "IFabricate.php";


/* 
    Внутри фабрик Используем DELETE, а не TRUNCATE, 
    так как есть связь с другими таблицами 
*/

$fabrica = new BaseFabricator;

$fabrica->create();