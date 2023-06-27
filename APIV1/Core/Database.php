<?php

class DB {
    private string $host;
    private string $port;
    private string $dbname;
    private string $user;
    private string $password;

    private $pdo; //Объект для PDO

    public function __construct(
        string $host = 'mysql', 
        string $port = '3306', 
        string $dbname = 'gamedata', 
        string $user = 'root', 
        string $password = 'gamedata')
    {
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ];
        $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;", $user, $password, $opt);
    }

    /* Реализует запрос без параметризации (не требует подготовки выражения) */
    public function query(string $sql) {
        $arrayReturn = [];
        $result = $this->pdo->query($sql);
        while($row = $result->fetch()) {
            array_push($arrayReturn, $row);
        }
        return $arrayReturn;
    }

    
}