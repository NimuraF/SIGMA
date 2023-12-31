<?php 

abstract class BaseFilter {

    
    protected array $whereConditions = [];
    protected array $whereOrConditions = [];

    public function __construct(array $filters)
    {

        /* Перебираем все фильтры из реквеста */
        foreach ($filters as $key=>$value) {

            /* Проверяем наличие такового фильтра в классе фильтрации */
            if(method_exists($this, $key)) {

                /* Вызываем сам фильтр */
                call_user_func([$this, $key], $value);

            }

        }
    }

    /* Возвращает условия фильтрации AND */
    public function filter() : array {
        return $this->whereConditions;
    }

    /* Возвращает условия фильтрации OR */
    public function filterOr() : array {
        return $this->whereOrConditions;
    }

}