<?php

final class Token {

    
    /* 
        Метод создания токенов 
    */
    static function createToken(array $data, string $tokenTable = "users_tokens", string $alg = "sha256") : string {

        /* Открываем соединение с БД */
        $DB = new DB();

        /* Достаём массив токенов из БД */
        $result = $DB->query("SELECT token FROM $tokenTable");


        /* Переменная, отвечаюшая за уникальность токена*/
        $unique = false;

        
        /* 
        Проверяем токен на уникальность, если же токен не уникален,
        то генерируем новый до тех пор, пока он не станет уникальным
        */
        while (!$unique) {
            $flag = true;
            $token = hash($alg, implode("", $data).microtime());
            foreach($result as $savedToken) {
                if(strcmp($token, $savedToken['token']) == 0) {
                    $flag = false;
                    break;
                }
            }
            if ($flag != false) {
                $unique = true;
            }
        }

        return $token;
    }



    /* 
        Метод, валидирующий токен и возвращающий id юзера, 
        если токен действительный, и false, если же не пройдена
        валидация
    */
    static function validateToken(?string $token, string $token_table = "users_tokens") : bool|int {

        if ($token !== NULL) {
            /* Открываем соединение с БД */
            $DB = new DB();

            /* Ищем в базе данных токен, переданный для валидирования */
            $result = $DB->select($token_table, ['token_id', 'user_id', 'created_at'])->where([['token', '=', $token]])->get();

            /* 
                Если количество возвращённых (затронутых) записей не равно 1, то выбрасываем ошибку,
                то же самое касается и случая, когда полученная кука была создана больше, чем 24 часа
                назад.
            */
            if (count($result) != 1 || (strtotime($result[0]['created_at']) - time()) > 0 ) {
                return false;
            }

            return $result[0]['user_id'];
        }

        return false;
    }


    /* 
        Метод, устанавливающий новый токен в БД
    */
    static function setNewToken(string $table, $user_id) : string|bool {

        $DB = new DB();

        $newToken = self::createToken([mt_rand()], $table);

        if ($DB->insert($table, ['user_id' => $user_id, 'token' => $newToken])) {
            return $newToken;
        }

        return false;

    }

}