<?php


/* GAMES */
Router::get('/', GamesController::class, 'allGames');
Router::get('/games', GamesController::class, 'allGames'); //Метод, возвращающий все записи из таблицы игр
Router::get('/games/{id}', GamesController::class, 'getGameInfo'); //Метод, возвращающий информацию об игре по её id
Router::post('/games/create', GamesController::class, 'loadGame')::middleware('auth|permissioncheck'); //Метод, позволяющий загрузить информацию о новой игре
Router::get('/games_genres', GamesController::class, 'loadAllGenres'); // Метод, отвечающий за загрузку всех жанров


/* NEWS */
Router::get('/news', NewsController::class, 'allNews'); //Метод, отвечающий за возврат всех новостей


/* USER */
Router::get('/user/{id}', UserController::class, 'userInfo'); //Метод, возвращающий всю информацию о пользователе
Router::post('/user/{id}/loadavatar', UserController::class, 'loadAvatar')::middleware('auth'); //Метод, отвечающий за загрузку аватара пользователя
Router::post('/user/{id}/loadbanner', UserController::class, 'loadBanner')::middleware('auth'); //Метод, отвечающий за загрузку баннера пользователя     


/* АВТОРИЗАЦИЯ И РЕГИСТРАЦИЯ */
Router::post('/registration', AuthController::class, 'createUser')::middleware('notauth'); //Маршрут для регистрации пользователя
Router::post('/authentication', AuthController::class, 'authentication')::middleware('notauth'); //Метод для аутентификации
Router::post('/authorization', AuthController::class, 'getAuthorizedUser')::middleware('auth'); //Метод, возвращающий текущего авторизованного пользователя 
