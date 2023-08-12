<?php


abstract class Controller {
    
    /* Метод преобразования и отправки информации в json-формате */
    public function json(array|bool $toJson = []) : string {
        
        /* 
            Если был передан параметр false,
            например, не удалось подготовить
            запрос, то возвращаем пустой массив
        */
        if (gettype($toJson) === "boolean") {
            $toJson = [];
        }

        /* Так же добавляем параметр о том, что доступ получен */
        $json = json_encode(['access' => true, 'data' => $toJson]);
        return $json;
    }

    /* Метод отправки ошибки при каком-то условии */
    static function errorMessage(string $errorMessage) : string {
        $error = json_encode(['access' => false, 'errorm' => $errorMessage]);
        return $error;
    }

    /* Метод для загрузки фильтров */
    public function loadFilters(string $filterName, array $filterParams = []) : object|false {
        $filterPath = "./APIV1/Filters/UsableFilters/".$filterName."Filter.php";
        if (file_exists($filterPath)) {
            include $filterPath;
            $filterClassName = $filterName."Filter";
            return new $filterClassName($filterParams);
        }
        return false;
    }
    
}