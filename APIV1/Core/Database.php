<?php

class DB {
    private string $host;
    private string $port;
    private string $dbname;
    private string $user;
    private string $password;

    private string $query = "";
    private array $paramsToPrepared = [];

    private $pdo; //Объект для PDO

    public function __construct(
        string $host = 'localhost', 
        string $port = '3306', 
        string $dbname = 'gamedata', 
        string $user = 'root', 
        string $password = 'kirik556')
    {
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ];
        $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;", $user, $password, $opt);
    }

    /* Реализует запрос без параметризации (не требует подготовки выражения) */
    public function query(string $sql) : array {
        $arrayReturn = [];
        $result = $this->pdo->query($sql);

        if ($result) {
            while($row = $result->fetch()) {
                array_push($arrayReturn, $row);
            }
        }

        return $arrayReturn;
    }

    public function select(string $from, array $columns = []) {

        $addColumns = "";

        /* Парсим требуемые колонки для select */
        if (sizeof($columns) > 0) {
            foreach ($columns as $column) {
                $addColumns .= $column.", ";
            }
            $addColumns = substr($addColumns, 0, -2);
        } else {
            $addColumns = "*";
        }


        /* Добавляем подготовленное выражение к итоговому запросу */
        $this->query .= "SELECT ".$addColumns." FROM ".$from;
        return $this;
    }

    public function update(string $table, string $id, array $values) {
        $setValues = "UPDATE $table SET";

        foreach ($values as $key=>$value) {
            $setValues .= " ".$key." = ? ";
            array_push($this->paramsToPrepared, $value);
        }


        $this->clearContainers();
    }

    /* Метод вставки новых данных в таблицу */
    public function insert(string $table, array $values = []) : bool {

        /* Формируем основну для запросов по дабвлению в таблицу */
        $insertQuery = "INSERT INTO $table (";
        $insertValues = "VALUES (";

        /* Разбираем параметры для последующей подготовки */
        foreach($values as $key=>$value) { 
            $insertQuery .= $key.",";
            array_push($this->paramsToPrepared, $value);
            $insertValues .= "?,";
        }

        /* Обрезаем последнюю запятую и закрываем определяемые параметры */
        $insertQuery = substr($insertQuery, 0, -1).") ".substr($insertValues, 0, -1).")";

        $this->query = $insertQuery;

        /* Подготавливаем и выполянем запрос к базе данных*/
        if ($update = $this->pdo->prepare($this->query)) {
            if(!$update->execute($this->paramsToPrepared)) {
                $this->clearContainers();
                return false;
            }
            $this->clearContainers();
            return true;
        }

        $this->clearContainers();
        return false;
    }

    public function where (array $conditions) {
        if (!empty($conditions)) {
            $whereConditions = [];
            foreach ($conditions as $condition) {
                array_push($whereConditions, $condition[0]." ".$condition[1]." ? ");
                array_push($this->paramsToPrepared, $condition[2]);
            }

            $whereConditionsQuery = implode(" AND ", $whereConditions);
            $whereConditionsQuery = " WHERE ".$whereConditionsQuery;
            $this->query .= $whereConditionsQuery;
        }
        return $this;
    }

    public function get() {
        if($get = $this->pdo->prepare($this->query)) {
            $arrayReturn = [];
            $get->execute($this->paramsToPrepared);
            foreach($get as $row) {
                array_push($arrayReturn, $row);
            }
            $this->clearContainers();
            return $arrayReturn;
        }
        $this->clearContainers();
        return false;
    }

    public function limit(int $page, int $count = 5) {

        if ($page >= 1) 
        {
            $offset = ($page - 1) * $count;
        } 
        else 
        {
            $offset = 0 * $count;
        }

        $this->query .= " LIMIT $offset, $count ";
        return $this;
    }


    /* Функция очистки контейнеров для повторных запросов */
    private function clearContainers() {
        $this->query = "";
        $this->paramsToPrepared = [];
    }
}