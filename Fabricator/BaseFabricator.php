<?php

class BaseFabricator {

    /* 
        ФАБРИКИ ДОЛЖНЫ БЫТЬ ЗАРЕГИСТРИРОВАНЫ
        СТРОГО В ОПРЕДЕЛЁННОМ ПОРЯДКЕ!
    */
    protected array $fabrics = [
        'GenresFabricator' => './Fabricator/Fabrics/GenresFabricator.php',
        'GamesFabricator' => './Fabricator/Fabrics/GamesFabricator.php',
        'GamesGenresFabricator' => './Fabricator/Fabrics/GamesGenresFabricator.php'
    ];


    public function __construct()
    {
        echo "\n".static::class." => ";
    }


    public function showResult(bool $result) {
        if ($result === true) {
            echo "success";
        } 
        else 
        {
            echo "error";
        }
    }

    public function create() {

        $currentFabricName = key($this->fabrics);
        $currentFabricPath = array_shift($this->fabrics);


        if ($currentFabricPath !== NULL) {

            require_once $currentFabricPath;

            $fabricator = new $currentFabricName;

            $fabricator->fabricate([$this, 'create']);
        }

    }

}