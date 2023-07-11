<?php

class GamesController extends Controller {




    /* Возвращает список всех игр */
    public function allGames (Request $request = new Request) {

        $DB = new DB;

        /* Номер страницы по умолчанию */
        $page = 1;
        

        /* Определяем, что нам нужны фильтры */
        include "./APIV1/Filters/UsableFilters/GameFilter.php";
        $filters = new GameFilter(isset($request->params) ? $request->params : []);


        /* Проверяем наличие параметра page в url */
        if (isset($request->params["page"])) 
        {
            $page = (int) $request->params["page"];
        } 


        return $this->json
        (
            $DB->select("games")->where( $filters->filter() )->limit($page)->get()
        );

    }




    /* Возвращает информацию о конкретной игре по её id (вместе с жанрами) */
    public function getGameInfo(string $id) {

        $DB = new DB;

        if($game = $DB->select("games", ['id', 'name', 'publisher', 'platform'])->where([
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
            if( $DB->insert('games', $newGame) ) 
            {

                $note = "";

                /* Проверяем на наличие изображения в request */
                if (isset($request->params['image'])) 
                {

                    /* Проверяем, чтобы загружаемый файл обязательно был картинкой */
                    if (Storage::validationImage($request->params['image'])) {

                        /* Если удалось сохранить в директорию на сервере */
                        if( ($pathToFile = Storage::save($request->params['image'], 'game_images')) !== "" ) {
                            
                            /* Обновляем картинку у игры */
                            if ($DB->update('games', ['image' => $pathToFile])->where([['name', '=', $request->params['name']]])->set()) {

                                $note = 'image succesfully updated';

                            } 
                            else 
                            {
                                $note = "failed to load image";
                            }

                        } 
                        else 
                        {
                            $note = "failed to load image";
                        }

                    } 
                    else 
                    {
                        $note = "failed to load image";
                    }

                }
                
                return $this->json(['success' => true, 'note' => $note]);

            }

            return $this->errorMessage("Something went wrong");

        }

        return $this->errorMessage("Not enough parameters");
        
    }
}