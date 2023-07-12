<?php

class GamesGenresFabricator extends BaseFabricator implements IFabricate {

    protected array $games_genres = []; 

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $games = $DB->select('games', ['name'])->get();
        $genres = $DB->select('genres', ['name'])->get();
        

        for ($i = 0; $i < 500; $i++) {
            $randomGame = $games[array_rand($games, 1)]['name'];
            $randomGenre = $genres[array_rand($genres, 1)]['name'];

            while(in_array(array('game_name' => $randomGame, 'genre_name' => $randomGenre), $this->games_genres)) {
                $randomGame = $games[array_rand($games, 1)]['name'];
                $randomGenre = $genres[array_rand($genres, 1)]['name'];
            }

            $this->games_genres[] = array('game_name' => $randomGame, 'genre_name' => $randomGenre);
        }

        $queryRow = [];

        foreach ($this->games_genres as $pair) {
            $queryRow[] = "('".$pair['game_name']."','".$pair['genre_name']."')";
        }

        $queryRowString = implode(',', $queryRow);

        $this->showResult($DB->queryTF("INSERT INTO games_genres (game_name, genre_name) VALUES $queryRowString"));
        
        return $next();

    }

}