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
                
                $result = $DB->update('users', ['avatar' => "gsdgsdg"])->where([['id', '=', $userID]])->get();
                
            } 
            else 
            {
                Router::redirect();
            }

        } 
        else 
        {
            Router::redirect();
        }

    }

}