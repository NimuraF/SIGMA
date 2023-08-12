<?php


/* GAMES */
Router::get('/', GamesController::class, 'allGames');
Router::get('/games', GamesController::class, 'allGames'); //Метод, возвращающий все записи из таблицы игр
Router::get('/games/{id}', GamesController::class, 'getGameInfo'); //Метод, возвращающий информацию об игре по её id
Router::post('/games/create', GamesController::class, 'loadGame')::middleware('auth|permissioncheck'); //Метод, позволяющий загрузить информацию о новой игре
Router::get('/genres', GamesController::class, 'loadAllGenres'); // Метод, отвечающий за загрузку всех жанров


/* LIBRARY */
Router::get('/user/{id}/library', LibraryController::class, 'showLibrary'); //Метод, отвечающий за отображение библиотеки пользователя
Router::get('/user/{id}/library/{game_id}', LibraryController::class, 'checkGameInLibrary'); //Метод, проверяющий, находится ли игра в библиотеке пользователя
Router::post('/add-game/{id}', LibraryController::class, 'addGameToLibrary')::middleware('auth|permissioncheck'); // Метод, отвечающий за добавление игры в библиотеку
Router::post('/remove-game/{id}', LibraryController::class, 'removeGameFromLibrary')::middleware('auth|permissioncheck'); //Метод, отвечающий за удаление игры из библиотеки


/* NEWS */
Router::get('/news', NewsController::class, 'allNews'); //Метод, отвечающий за возврат всех новостей


/* ARTICLES */
Router::get('/articles', ArticlesController::class, 'allArticles'); //Метод, возвращающий список всех статей
Router::post('/articles/create', ArticlesController::class, 'createArticle')::middleware('auth|permissioncheck'); //Метод, позволяющий создать новую новость


/* USER */
Router::get('/user/top', UserController::class, 'topUsers'); //Метод, возвращающий топ пользователей (по уровню)
Router::get('/user/{id}', UserController::class, 'userInfo'); //Метод, возвращающий всю информацию о пользователе
Router::post('/user/{id}/loadavatar', UserController::class, 'loadAvatar')::middleware('auth|permissioncheck'); //Метод, отвечающий за загрузку аватара пользователя
Router::post('/user/{id}/loadbanner', UserController::class, 'loadBanner')::middleware('auth|permissioncheck'); //Метод, отвечающий за загрузку баннера пользователя     


/* АВТОРИЗАЦИЯ И РЕГИСТРАЦИЯ */
Router::post('/registration', AuthController::class, 'createUser')::middleware('notauth')::excludeCSRF(); //Маршрут для регистрации пользователя
Router::post('/authentication', AuthController::class, 'authentication')::middleware('notauth')::excludeCSRF(); //Метод для аутентификации
Router::post('/authorization', AuthController::class, 'getAuthorizedUser')::middleware('auth')::excludeCSRF(); //Метод, возвращающий текущего авторизованного пользователя 
Router::post('/logout', AuthController::class, 'logout')::middleware('auth'); //Метод для разлогина
