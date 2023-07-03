<?php

class GamesController extends Controller {

    /* Возвращает список всех игр */
    public function allGames ($request = new Request) {
        $DB = new DB;
        if (isset($request->params[0]["page"])) {
            return $this->showJson
            (
                $DB->select("games")->limit( (int) $request->params[0]["page"] )->get()
            );
        } else {
            return $this->showJson
            (
                $DB->select("games")->limit(0)->get()
            );
        }
    }

    /* Возвращает информацию о конкретной игре по её id */
    public function getGameInfo(string $id) {
        $DB = new DB;
        if($result = $DB->select("games", ['id', 'name', 'publisher', 'platform'])->where([
            ["id", "=", $id]
        ])->get()) 
        {
            return $this->showJson($result);
        }
    }

}