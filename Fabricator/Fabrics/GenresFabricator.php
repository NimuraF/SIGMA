<?php

class GenresFabricator extends BaseFabricator implements IFabricate {


    public function fabricate(callable $next)
    {

        $DB = new DB();

        $DB->queryTF('DELETE from genres');
        $DB->queryTF('ALTER TABLE genres AUTO_INCREMENT = 1');

        $sendGenres = [];

        foreach ($this->genres as $genre) {
            $sendGenres[] = "('".$genre."')";
        }

        
        $sendRow = implode(',', $sendGenres);

        $query = "INSERT INTO genres (name) VALUES $sendRow";
        

        $this->showResult($DB->queryTF($query));
    

        return $next();
    }


    private array $genres = [
        'Action',
        'Shooter',
        'FPS',
        'MOBA',
        'MMORPG',
        'Simulator',
        'Strategy',
        'RPG',
        'Sport',
        'Racing'
    ];

}