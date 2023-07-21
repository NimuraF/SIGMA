<?php

/* ФАБРИКА ИГР */

class GamesFabricator extends BaseFabricator implements IFabricate {

    /* 'unique_name' => ['publisher' => $publisher, 'platform' => $platform] */
    private array $createdgames = [];

    public function fabricate(callable $next) {

        $DB = new DB();

        $DB->queryTF('DELETE FROM games');
        $DB->queryTF('ALTER TABLE games AUTO_INCREMENT = 1');

        /* Подтягиваем faker */
        $faker = Faker\Factory::create("ru_RU");

        /* Генерируем определённое кол-во записей для последующей вставки в БД */
        for ($i = 0; $i < 100000; $i++) {

            $name = $faker->userName();
            $publisher = $faker->company();
            $platform = $this->platforms[array_rand($this->platforms)];
            
            /* Если нашли ключ (имя игры) в массиве, то генерим новый*/
            while(array_key_exists($name, $this->createdgames)) {
                $name = $faker->userName();
            }

            $this->createdgames[$name] = ['publisher' => $publisher, 'platform' => $platform];

        }

        /* Инициализируем массив для последующего объединения в один SQL-запрос */
        $queryRow = [];

        /* Перебираем все элементы и устанавливаем им SQL-формат синтаксиса */
        foreach($this->createdgames as $name=>$info) {

            $queryRow[] = "('".$name."',"."'".$info['publisher']."',"."'".$info['platform']."')";

        }

        $queryRowString = implode(',', $queryRow);


        $this->showResult($DB->queryTF("INSERT INTO games (name, publisher, platform) VALUES $queryRowString"));

        return $next();

    }

    private array $platforms = [
        'PC',
        'PlayStation',
        'XBOX'
    ];

}