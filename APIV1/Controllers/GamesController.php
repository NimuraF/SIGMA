<?php

class GamesController extends Controller {

    /* Возвращает список всех игр */
    public function allGames () {
        $DB = new DB;
        return $this->showJson($DB->query('SELECT * from games'));
    }

    public function generateRandom() {
        
    }

    public function getGameInfo(Request $request) {
        $arrayReturn = [];
        $gameID = explode('/', $_SERVER['REQUEST_URI']);
        $DB = new DB;
        $gameInfo = $DB->query('SELECT * from games WHERE id = 1');
        array_push($arrayReturn, ['game_info' => $gameInfo]);
        $param = $gameInfo[0]['name'];
        $gameGenres = $DB->query("SELECT genre_name from games_genres WHERE game_name = '$param'");
        array_push($arrayReturn, ['genres' => $gameGenres]);
        return $this->showJson($arrayReturn);
    }
}