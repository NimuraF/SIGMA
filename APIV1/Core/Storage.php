<?php

final class Storage {

    static private string $path = "./Storage/";


    /* Метод сохранения изображений */
    static function save(array $fileInfo, string $directory = "") : string {

        $path = self::$path.$directory."/";

        /* Проверяем на существование директории */
        if (!file_exists($path)) {
            mkdir($path);
        }

        /* Определяем расширение */
        $extension = explode('/', $fileInfo['type'])[1];

        /* 
            Если удалось переместить из временного хранилища в
            основную директорию, то возвращем путь к этому самому
            файлу, если же нет - пустую строку
        */
        if(move_uploaded_file($fileInfo['tmp_name'], $path = $path.self::getRandomFileName($extension))) {
            
            return substr($path, 2);
        }

        return "";
    }



    /* Метод для удаления изображений */
    static function delete($filePath) : bool {
        if (file_exists($filePath)) {
            if(unlink($filePath)) {
                return true;
            }
        }
        return false;
    }



    
    /*
        Метод для генерации уникального имени файла
        в текущей корневой директории, т.е. в self::$path
    */
    static function getRandomFileName(string $extension) : string {

        /* Генерим новую строку для имени файла */
        $name = uniqid();

        /* Проверяем на уникальность */
        while (file_exists($name.$extension)) {
            $name = uniqid();
        }
        
        return $name.".".$extension;
    }





    /*
        Метод, валидириющий файл, поступивший во временное
        хранилище пыхи, на предмет того, что это картиночка (изображение) 
        ((image))
    */
    static function validationImage(array $fileInfo) : bool {
        
        /* Массив разрешённых форматов */
        $allowFormats = ['jpeg', 'jpg', 'png'];
        
        /* Перебираем все разрешённые форматы */
        foreach ($allowFormats as $format) { 

            /* Если нашли соответствие по формату */
            if ($fileInfo['type'] === "image/".$format) {

                return true;

            }

        }

        return false;
    }







}