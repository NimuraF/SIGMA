<?php

class Token {
    private $token;
    private $validated = false;
    private int|bool $currentUser = false;

    /* 
    При вызове конструктора проверяем наличие токена в куках,
    после чего сразу вызываем на пришедший токен метод валидации
    */
    public function __construct() {
        if (isset($_COOKIE['token'])) {

            /* Извлекаем токе из кук, если он есть*/
            $this->token = $_COOKIE['token'];

            /* Сразу валидириуем токен */
            $this->validateToken($this->token);

        } else {

            $this->token = false;
        }
    }

    /* Метод создания токена для взаимодействия с защищёнными ресурсами API */
    public function createToken(array $data, string $alg = "sha256") : string {

        /* Генерируем новый хэш-токен */
        $DB = new DB();

        /* Достаём массив токенов из БД */
        $result = $DB->query("SELECT token FROM tokens");


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
        
        /* Записываем полученное значение токена в свойство */
        $this->token = $token;

        return $this->getToken();
    }

    /* Метод, валидирующий токен и возвращающий true, если токен действительынй и false, если нет */
    public function validateToken(string $token) : bool {

        /* Открываем соединение с БД */
        $DB = new DB();

        /* Ищем в базе данных токен, переданный для валидирования */
        $result = $DB->select('tokens', ['token_id'])->where([['token', '=', $token]])->get();

        /* Если количество возвращённых (затронутых) записей не равно 1, то выбрасываем ошибку */
        if (count($result) != 1) {
            $this->validated = false;
            return false;
        }

        $this->validated = true;
        return true;
    }

    /* Метод, возвращающий текущий статус валидации токена */
    public function getValidated() : bool {
        return $this->validated;
    }

    /* Метод, возвращающий значение текущего токена */
    public function getToken() {
        return $this->token;
    }

    /* Метод, возвращающий id авторизованного юзера или false, если его нет */
    public function getUser() : bool|int {
        return $this->currentUser;
    }
}