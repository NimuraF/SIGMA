<?php


/* GAMES */
Router::get('/', GamesController::class, 'allGames');
Router::get('/games', GamesController::class, 'allGames'); //Метод, возвращающий все записи из таблицы игр
Router::get('/games/{id}', GamesController::class, 'getGameInfo'); //Метод, возвращающий информацию об игре по её id
Router::post('/games/create', GamesController::class, 'loadGame')::middleware('auth|permissioncheck'); //Метод, позволяющий загрузить информацию о новой игре
Router::get('/genres', GamesController::class, 'loadAllGenres'); // Метод, отвечающий за загрузку всех жанров


/* NEWS */
Router::get('/news', NewsController::class, 'allNews'); //Метод, отвечающий за возврат всех новостей
Router::get('/news/{id}', NewsController::class, 'ggg');


/* ARTICLES */
Router::get('/articles', ArticlesController::class, 'allArticles'); //Метод, возвращающий список всех статей
Router::post('/articles/create', ArticlesController::class, 'createArticle')::middleware('auth|permissioncheck'); //Метод, позволяющий создать новую новость


/* USER */
Router::get('/user/{id}', UserController::class, 'userInfo'); //Метод, возвращающий всю информацию о пользователе
Router::post('/user/{id}/loadavatar', UserController::class, 'loadAvatar')::middleware('auth|permissioncheck'); //Метод, отвечающий за загрузку аватара пользователя
Router::post('/user/{id}/loadbanner', UserController::class, 'loadBanner')::middleware('auth|permissioncheck'); //Метод, отвечающий за загрузку баннера пользователя     


/* АВТОРИЗАЦИЯ И РЕГИСТРАЦИЯ */
Router::post('/registration', AuthController::class, 'createUser')::middleware('notauth')::excludeCSRF(); //Маршрут для регистрации пользователя
Router::post('/authentication', AuthController::class, 'authentication')::middleware('notauth')::excludeCSRF(); //Метод для аутентификации
Router::post('/authorization', AuthController::class, 'getAuthorizedUser')::excludeCSRF(); //Метод, возвращающий текущего авторизованного пользователя 
Router::post('/logout', AuthController::class, 'logout')::middleware('auth')::excludeCSRF(); //Метод для разлогина
