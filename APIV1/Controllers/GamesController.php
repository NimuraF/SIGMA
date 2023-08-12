<?php

class GamesController extends Controller {


    /* Возвращает список всех игр */
    public function allGames (Request $request) {

        $DB = new DB;

        /* Номер страницы по умолчанию */
        $page = isset($request->params["page"]) ? (int) $request->params["page"] : 1;
        

        /* Определяем, что нам нужны фильтры */
        $filters = $this->loadFilters('Game', $request->params);


        /* Если передан параметр, отвечающий за жанр, то строим выборку на join-ах */
        if (isset($request->params["genres"])) {
            $genresCount = sizeof($request->params["genres"]);
            return $this->json(
                $DB->select("games")
                    ->innerJoin("games_genres", 'games_genres.game_name', '=', 'games.name')
                    ->where( $filters !== false ? $filters->filter() : [] )
                    ->whereOr( $filters !== false ? $filters->filterOr() : [])
                    ->groupBy(['games.name'])
                    ->having("COUNT(games.name) = $genresCount")
                    ->limit($page, 50)
                    ->get()
            );
        }
        

        return $this->json
        (
            $DB->select("games")
                ->where( $filters->filter() )
                ->limit($page, 50)
                ->get()
        );

    }



    /* Возвращает информацию о конкретной игре по её id (вместе с жанрами) */
    public function getGameInfo(string $id) {

        $DB = new DB;

        if($game = $DB->select("games", ['id', 'name', 'publisher', 'platform', 'image', 'about'])->where([
            ["id", "=", $id]
        ])->get()) 
        {
            /* Получаем жанры по имени игры */
            $genres = $DB->select("games_genres", ['genre_name'])
            ->where([
                ['game_name', '=', $game[0]['name']]
            ])
            ->get();

            
            return $this->json(['game' => $game[0], 'genres' => $genres]);
        }
    }


    
    /* Метод для загрузки новой игры в БД */
    public function loadGame(Request $request) {
        /*
            Проверяем на наличие в реквеста полей, обязательных
            для текущего контекста таблицы БД, в случае с играми
            речь идёт о name, publisher и platform
        */
        if (isset($request->params['name']) && isset($request->params['publisher']) && isset($request->params['platform'])) 
        {

            $DB = new DB();

            /* Определяем параметры новой игры */
            $newGame = [
                'name' => $request->params['name'],
                'publisher' => $request->params['publisher'],
                'platform' => $request->params['platform'],
                'release_date' => isset($request->params['release_date']) ? $request->params['release_date'] : NULL
            ];

            /* Проверяем на успешность добавление записи в таблицу */
            if( $DB->insertOrUpdate('games', $newGame) ) 
            {

                $notes = [];

                /* Проверяем на наличие изображения в request и валидируем картинку, а так же извлекаем картинку из БД */
                if 
                (
                    isset($request->params['image']) 
                    && Storage::validationImage($request->params['image'], 400, 700)
                    && ($pathToFile = Storage::save($request->params['image'], 'game_images')) !== ""
                    && ( $currentImage = $DB->select('games', ['image'])->where([['name', '=', $request->params['name']]])->get() ) !== false
                ) 
                {

                    /* Если картинка уже была установлена, то удаляем её */
                    if ($currentImage[0]['image'] !== NULL) {
                        Storage::delete($currentImage[0]['image']);
                    }

                    /* Обновляем картинку у игры */
                    if (!$DB->update('games', ['image' => $pathToFile])->where([['name', '=', $request->params['name']]])->set()) {
                        $notes[] = "Failed to load image to DB";
                    }
                } 
                else 
                {
                    $notes[] = "Incorrect image file";
                }

                /* Проверяем на наличие жанров при добавлении игры */
                if ( isset($request->params['genres']) ) {
                    /* Перебираем все жанры, пришедшие от пользователя */
                    foreach($request->params['genres'] as $genre) {
                        if (!$DB->insert('games_genres', ['game_name' => $request->params['name'], 'genre_name' => $genre])) {
                            $notes[] = "can't add genre ".$genre." to game ".$request->params['name'];
                        }
                    }
                }
                
                return $this->json($notes);
            }
            return Controller::errorMessage("Something went wrong");
        }
        return Controller::errorMessage("Not enough parameters");
    }


    
    /* Возвращает словарь жанров */
    public function loadAllGenres() {

        $DB = new DB();
        
        $genres = $DB->select('genres', ['name'])->get();

        return $this->json($genres);

    }


    
}