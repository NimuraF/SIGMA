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
        string $host = Configuration\Configuration::DB_HOST, 
        string $port = Configuration\Configuration::DB_PORT, 
        string $dbname = Configuration\Configuration::DB_NAME, 
        string $user = Configuration\Configuration::DB_USER, 
        string $password = Configuration\Configuration::DB_PASS)
    {
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ];
        $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;", $user, $password, $opt);
    }

    /* 
        Реализует запрос без параметризации 
        (не требует подготовки выражения) 
    */
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


    /*
        Функция, возвращающая id последней записи 
    */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }


    /*
        Реализует запрос без параметризации и
        возвращает только true или false в зависимости
        от успешности / неуспешности запроса
    */
    public function queryTF(string $sql) : bool {

        if ($this->pdo->query($sql)) {
            return true;
        }

        return false;
    }




    /* 
        Метод для исполнения SELECT запроса,
        возвращает текущий контекст DB
    */
    public function select(string $from, array $columns = []) : DB {

        $addColumns = "";

        /* Парсим требуемые колонки для select */
        if (sizeof($columns) > 0) {
            foreach ($columns as $column) {
                $addColumns .= $from.".".$column.", ";
            }
            $addColumns = substr($addColumns, 0, -2);
        } else {
            $addColumns = "$from.*";
        }


        /* Добавляем подготовленное выражение к итоговому запросу */
        $this->query .= "SELECT ".$addColumns." FROM ".$from;
        return $this;
    }





    /* 
        Метод для выполнения INNER JOIN операции,
        возвращает текущий контекст DB
    */
    public function innerJoin(string $table, string $column = "", string $condition = "", string $equal = "") : DB {

        /* Сразу определяем контекст, с какой таблицей будем соединять */
        $this->query .= " INNER JOIN ".$table;

        /* Если присутствует условие соединения, то заполняем и его */
        if ($column !== "") {
            $this->query .= " ON ".$column." ".$condition." ".$equal;
        }

        return $this;
    }





    /*
        Метод для упорядочивания столбцов
        в выборке по заданному параметру,
        возвращает контекст текущего DB
    */
    public function orderBy(string $column, string $orientation = 'ASC') {
        $this->query .= " ORDER BY $column $orientation";
        return $this;
    }







    public function update(string $table, array $values) : DB {
        $setValues = "UPDATE $table SET";

        foreach ($values as $key=>$value) {
            $setValues .= " ".$key." = ? ";
            array_push($this->paramsToPrepared, $value);
        }

        $this->query = $setValues;

        return $this;
    }




    /* 
        Метод вставки новых данных в таблицу, при
        успехе возвращает true, в то время как при
        неудаче возвращается false
    */
    public function insert(string $table, array $values = []) : bool {

        /* Формируем основу для запросов по дабвлению в таблицу */
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

                /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
                $this->clearContainers();

                return false;
            }

            /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
            $this->clearContainers();
            return true;
        }

        $this->clearContainers();
        return false;
    }







    
    /* 
        Метод параметризации основных команд SQL
        возвращает контекст текущего DB, по умолчанию
        конкатенирует параметры с условием AND
    */
    public function where (?array $conditions) : DB {

        /* 
            Проверяем, что поступивший список словий не пустой,
            при этом условия должны передавать в виде двумерного массива,
            где каждое условие задаётся в следующем виде в отдельном
            массиве: [УСЛОВИЕ, ОПЕРАТОР СРАВНЕНИЯ, ЗНАЧЕНИЕ]
        */

        if (!empty($conditions)) {

            $whereConditions = [];

            /* Перебирааем все вложенные массивы (условия) */
            foreach ($conditions as $condition) {

                array_push($whereConditions, $condition[0]." ".$condition[1]." ? ");

                array_push($this->paramsToPrepared, $condition[2]);

            }

            /* Формируем итоговую строку */
            $whereConditionsQuery = implode(" AND ", $whereConditions);
            $whereConditionsQuery = " WHERE ".$whereConditionsQuery;
            $this->query .= $whereConditionsQuery;
        }

        /* Возвращаем контекст текущего класса */
        return $this;
    }



    
    /* 
        Метод параметризации основных команд SQL
        возвращает контекст текущего DB, по умолчанию
        конкатенирует параметры с условием OR
    */
    public function whereOr (?array $conditions) : DB {

        if (!empty($conditions)) {

            $whereConditions = [];

            /* Перебирааем все вложенные массивы (условия) */
            foreach ($conditions as $condition) {

                array_push($whereConditions, $condition[0]." ".$condition[1]." ? ");

                array_push($this->paramsToPrepared, $condition[2]);

            }

            /* Формируем итоговую строку */
            $whereConditionsQuery = implode(" OR ", $whereConditions);


            /* Проверяем, применялась ли уже до этого директива WHERE в строке запроса */
            if(strpos($this->query, "WHERE") === false) {
                $whereConditionsQuery = " WHERE ".$whereConditionsQuery;
            } else {
                $whereConditionsQuery = " AND (".$whereConditionsQuery.")";
            }
            

            $this->query .= $whereConditionsQuery;
        }

        /* Возвращаем контекст текущего класса */
        return $this;
    }




    /* 
        Метод для группировки запроса,
        возвращает контекст текущего DB
    */
    public function groupBy(array $columns = []) : DB {

        $this->query .= " GROUP BY ";

        foreach ($columns as $column) {
            $this->query .= $column;
        } 

        return $this;
    }




    /*
        Метод для указания особых параметров
        группировки, указывается после метода
        groupBy, возвращает контекс текущего DB
    */
    public function having(string $condition) : DB {

        $this->query .= " HAVING ".$condition;

        return $this;
    }






    /* 
        Завершающий метод для параметра SELECT, 
        возвращает массив данных при успехе, при
        неудаче - false
    */
    public function get() : array|bool {

        /* Проверяем, удалось ли подготовить запрос */
        if($get = $this->pdo->prepare($this->query)) {

            $arrayReturn = [];


            /* Разбираем подготовленные параметры */
            $get->execute($this->paramsToPrepared);

            /* Парсим полученные результаты */
            foreach($get as $row) {
                array_push($arrayReturn, $row);
            }

            /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
            $this->clearContainers();

            /* В результате взвращаем массив*/
            return $arrayReturn;
        }

        /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
        $this->clearContainers();

        /* При неудаче возвращаем false */
        return false;
    }



    /*
        Завершающий метод для параметров UPDATE и DELETE, 
        возвращает true при успехе, а при неудаче - false
    */
    public function set() : bool {

        if($set = $this->pdo->prepare($this->query)) {

            /* Разбираем подготовленные параметры */
            if($set->execute($this->paramsToPrepared)) {

                /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
                $this->clearContainers();

                /* В результате взвращаем массив*/
                return true;
            }
        }

        /* Чистим контейнеры (основную строку query и массив подготовленных параметров) */
        $this->clearContainers();

        /* При неудаче возвращаем false */
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
    private function clearContainers() : void {
        $this->query = "";
        $this->paramsToPrepared = [];
    }


}