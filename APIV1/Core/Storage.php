<?php

use Configuration\Configuration;

final class Storage {

    static private string $path = Configuration::STORAGE_PATH;


    /* 
        Метод сохранения изображений, при успешном сохранении
        возвращет строку, содержащую путь к файлу на сервере,
        при неудаче вовзвращает пустую строку
    */
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
        хранилище пыхи, на предмет того, что это image, допускает
        передачу доп. параметров, отвечающих за размер в пикселях/байтах
    */
    static function validationImage
    (
        array $fileInfo, 
        /* Ширина изображения */ int $maxWidth = Configuration::IMAGE_MAX_WIDTH_DEFAULT, 
        /* Высота изображения */ int $maxHeight = Configuration::IMAGE_MAX_HEIGHT_DEFAULT, 
        /* Размер изображения */ int $maxSize = Configuration::IMAGE_MAX_SIZE
    ) : bool 
    {
        
        /* Массив разрешённых форматов */
        $allowFormats = ['jpeg', 'jpg', 'png', 'gif'];
        
        /* Перебираем все разрешённые форматы */
        foreach ($allowFormats as $format) { 

            /* Если нашли соответствие по формату, переходим к анализу доп. параметров */
            if ($fileInfo['type'] === "image/".$format) 
            {

                /* Получаем текущий параметры размера изображения из временной папки */
                $imageInfo = getimagesize($fileInfo['tmp_name']);
                
                if($imageInfo[0] <= $maxWidth &&  $imageInfo[1] <= $maxHeight && $fileInfo['size'] <= $maxSize) 
                {
                    return true;
                }
            }
        }

        return false;
    }







}