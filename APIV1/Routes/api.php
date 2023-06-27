<?php




/* GAMES */
Router::get('/', GamesController::class, 'allGames');
Router::get('/games', GamesController::class, 'allGames'); //Метод, возвращающий все записи из таблицы игр
Router::get('/games/{id}', GamesController::class, 'getGameInfo'); //Метод, возвращающий информацию об игре по её id