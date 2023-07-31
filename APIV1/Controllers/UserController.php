<?php

class UserController extends Controller {

    /* Метод, отвечающий за загрузку аватара */
    public function loadAvatar(Request $request, string $id) {

        /* Проверяем на наличие самого свойства с картинкой в HTTP-запросе */
        if (isset($request->params['image'])) {

            $DB = new DB();

            /* Проверяем на соответствие id, переданых в ссылке и авторизованного по токену пользователя */
            if ($id == $request->user_id) 
            {
                if ( Storage::validationImage($request->params['image'], 300, 600) ) {

                    if( ($path = Storage::save($request->params['image'], 'avatars')) !== "") {

                        /* Если в базе данных уже хранится информация о картинке, то мы её удаляем */
                        if ( ($oldAvatar = $DB->select('users', ['avatar'])->where([['id', '=', $request->user_id]])->get()[0]['avatar']) !== NULL ) {
                            Storage::delete($oldAvatar);
                        }


                        /* Если удалось сохранить запись в базу данных */
                        if ($DB->update('users', ['avatar' => $path])->where([['id', '=', $request->user_id]])->set()) {
                            return $this->json();
                        }

                    }
                    return Controller::errorMessage("Oops, something went wrong while uploading the image");
                }
                return Controller::errorMessage('Wrong file parameters');
            } 
            return Controller::errorMessage('Wrong user parameters');
        } 
        return Controller::errorMessage('Failed to add image to request');
    }


    /* Метод, отвечающий за получение информации о пользователе */
    public function userInfo(string $id) {

        $DB = new DB();

        if ( $userInfo = $DB->select('users', ['id', 'name', 'avatar', 'banner'])->where([['id', '=', $id]])->get() ) 
        {
            return $this->json($userInfo);
        }

        return Controller::errorMessage("User doesn't exists");

    }


    /* Метод, отвечающий за загрузку баннера */
    public function loadBanner(Request $request, string $id) {

        if (isset($request->params['image'])) {

            $DB = new DB();

            if ($id == $request->user_id) 
            {

                if ( Storage::validationImage($request->params['image'], 1500, 300) ) {

                    if( ($path = Storage::save($request->params['image'], 'banners')) !== "") {


                        /* Если в базе данных уже хранится информация о картинке, то мы её удаляем */
                        if ( ($oldAvatar = $DB->select('users', ['banner'])->where([['id', '=', $request->user_id]])->get()[0]['banner']) !== NULL ) {
                            Storage::delete($oldAvatar);
                        }


                        /* Если удалось сохранить запись в базу данных */
                        if ($DB->update('users', ['banner' => $path])->where([['id', '=', $request->user_id]])->set()) {
                            return $this->json();
                        }

                    }
                    return Controller::errorMessage("Oops, something went wrong while uploading the image");
                }
                return Controller::errorMessage('Wrong file parameters');
            } 
            return Controller::errorMessage('Wrong user parameters');
        } 
        return Controller::errorMessage('Failed to add image to request');
    }






}