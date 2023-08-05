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

}