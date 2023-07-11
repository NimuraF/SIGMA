<?php

class GameFilter extends BaseFilter {

    public function name (string $name) {
        array_push($this->whereConditions, ['name', 'LIKE', "%$name%"]);
    }

    public function publisher (string $publisher) {
        array_push($this->whereConditions, ['publisher', 'LIKE', "%$publisher%"]);
    }

    public function release_date (array $date) {

        /* Начальная дата */
        if (key_exists("min", $date)) {
            $dateMin = $date['min'];
            array_push($this->whereConditions, ['release_date', '>=', "$dateMin"]);
        }

        /* Конечная дата */
        if(key_exists("max", $date)) {
            $dateMax = $date['max'];
            array_push($this->whereConditions, ['release_date', '<=', "$dateMax"]);
        }


    }


}