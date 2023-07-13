<?php

class UserController extends Controller {

    /* Метод, отвечающий за загрузку аватара */
    public function loadAvatar(Request $request, string $id) {


        /* Проверяем на наличие самого свойства с картинкой в HTTP-запросе */
        if (isset($request->params['image'])) {

            $DB = new DB();


            /* Проверяем на соответствие id, переданых в ссылке и из базы данных по токену */
            if ($id == ($userID = $DB->select('tokens', ['user_id'])->where([['token', '=', $request->auth]])->get()[0]['user_id'])) 
            {

                if ( Storage::validationImage($request->params['image']) ) {
                
                    $imageInfo = getimagesize($request->params['image']['tmp_name']);

                    if 
                    (
                        /* Ширина изображения */ $imageInfo[0] < 300 && 
                        /* Высота изображения */ $imageInfo[1] < 600 &&
                        /* Размер изображения (байт) */ $request->params['image']['size'] < 40000
                    ) 
                    {

                        if( ($path = Storage::save($request->params['image'], 'avatars')) !== "") {

                            $isLoaded = false;

                            /* Если в базе данных уже хранится информация о картинке, то мы её удаляем */
                            if ( ($oldAvatar = $DB->select('users', ['avatar'])->where([['id', '=', $userID]])->get()[0]['avatar']) !== NULL ) {
                                Storage::delete($oldAvatar);
                            }


                            /* Если удалось сохранить запись в базу данных */
                            if ($DB->update('users', ['avatar' => $path])->where([['id', '=', $userID]])->get()) {

                                return $this->json(['success' => true]);

                            }

                        }

                    } 
                    else 
                    {
                        return Controller::errorMessage('Wrong parameters');
                    }
                
                }
            } 
            else 
            {
                return Controller::errorMessage('Wrong parameters');
            }

        } 
        else 
        {
            return Controller::errorMessage('Wrong parameters');
        }

    }


    /* Метод, отвечающий за получение информации о пользователе */
    public function userInfo(string $id) {

        $DB = new DB();


        if ($userInfo = $DB
                ->select('users', ['id', 'name', 'avatar', 'banner'])
                ->where([['id', '=', $id]])
                ->get()
            ) 
        {
            return $this->json($userInfo);
        }

        return Controller::errorMessage("User doesn't exists");

    }


    /* Метод, отвечающий за загрузку баннера */
    public function loadBanner(Request $request, string $id) {

        if (isset($request->params['image'])) {

            $DB = new DB();

            if ($id == ($userID = $DB->select('tokens', ['user_id'])->where([['token', '=', $request->auth]])->get()[0]['user_id'])) 
            {

                if ( Storage::validationImage($request->params['image']) ) {
                
                    $imageInfo = getimagesize($request->params['image']['tmp_name']);

                    if 
                    (
                        /* Ширина изображения */ $imageInfo[0] < 2100 && 
                        /* Высота изображения */ $imageInfo[1] < 600 &&
                        /* Размер изображения (байт) */ $request->params['image']['size'] < 40000
                    ) 
                    {

                        if( ($path = Storage::save($request->params['image'], 'banners')) !== "") {

                            $isLoaded = false;

                            /* Если в базе данных уже хранится информация о картинке, то мы её удаляем */
                            if ( ($oldAvatar = $DB->select('users', ['banner'])->where([['id', '=', $userID]])->get()[0]['banner']) !== NULL ) {
                                Storage::delete($oldAvatar);
                            }


                            /* Если удалось сохранить запись в базу данных */
                            if ($DB->update('users', ['banner' => $path])->where([['id', '=', $userID]])->get()) {

                                return $this->json(['success' => true]);

                            }

                        }

                    } 
                    else 
                    {
                        return Controller::errorMessage('Wrong parameters');
                    }
                
                }
            } 
            else 
            {
                return Controller::errorMessage('Wrong parameters');
            }

        } 
        else 
        {
            Controller::errorMessage("Wrong parameters!");
        }

    }

}