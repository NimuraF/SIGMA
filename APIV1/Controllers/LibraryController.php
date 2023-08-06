<?php

class LibraryController extends Controller {


    public function addGameToLibrary(Request $request, string $game_id) {

        $DB = new DB();

        if 
        (
            $DB->insert('users_library', [
            'user_id' => $request->user_id, 
            'game_id' => $game_id, 
            'rating' => isset($request->params['rating']) ? $request->params['rating'] : NULL
            ])
        ) 
        {
            return $this->json(['game_id' => $game_id]);
        }

        return Controller::errorMessage("Failed to add game to library");
    }



    public function showLibrary(Request $request, string $user_id) {

        $DB = new DB();

        $page = isset($request->params["page"]) ? (int) $request->params["page"] : 1;

        $userLibrary = $DB->select('games')
            ->innerJoin('users_library', 'users_library.game_id', '=', 'games.id')
            ->where([['users_library.user_id', '=', $user_id]])
            ->limit($page, 50)
            ->get();

        

        if ($userLibrary !== false) {
            return $this->json($userLibrary);
        }

        return Controller::errorMessage("Failed to load library");
    }

}