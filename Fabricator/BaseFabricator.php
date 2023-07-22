<?php

class BaseFabricator {

    /* 
        ФАБРИКИ ДОЛЖНЫ БЫТЬ ЗАРЕГИСТРИРОВАНЫ
        СТРОГО В ОПРЕДЕЛЁННОМ ПОРЯДКЕ!
    */
    protected array $fabrics = [
        'GenresFabricator' => './Fabricator/Fabrics/GenresFabricator.php',
        'GamesFabricator' => './Fabricator/Fabrics/GamesFabricator.php',
        'GamesGenresFabricator' => './Fabricator/Fabrics/GamesGenresFabricator.php',
        'CategoriesForCommentsFabricator' => './Fabricator/Fabrics/CategoriesForCommentsFabricator.php',
        'UsersFabricator' => './Fabricator/Fabrics/UsersFabricator.php',
        'RolesFabricator' => './Fabricator/Fabrics/RolesFabricator.php',
        'UsersRolesFabricator' => './Fabricator/Fabrics/UsersRolesFabricator.php',
        'ArticlesFabricator' => './Fabricator/Fabrics/ArticlesFabricator.php',
        'TagsFabricator' => './Fabricator/Fabrics/TagsFabricator.php'
    ];


    public function __construct()
    {
        if (static::class !== "BaseFabricator") {
            echo "\n\033[1;33m".static::class." \033[0m=> " ;
        }
    }


    public function showResult(bool $result) {
        if ($result === true) {
            echo "\033[3;32msuccess\033[0m";
        } 
        else 
        {
            echo "\033[1;31merror\033[0m";
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