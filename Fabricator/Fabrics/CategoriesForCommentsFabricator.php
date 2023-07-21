<?php

/* ФАБРИКА КАТЕГОРИЙ ДЛЯ КОММЕНТАРИЕВ */

class CategoriesForCommentsFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next) {

        $DB = new DB();

        $DB->queryTF('DELETE FROM categories_for_comments');
        $DB->queryTF('ALTER TABLE categories_for_comments AUTO_INCREMENT = 1');

        $sendCategories = [];

        foreach($this->categories as $category) {
            $sendCategories[] = "('".$category."')";
        }

        $sendRow = implode(",", $sendCategories);

        $query = "INSERT INTO categories_for_comments (category_name) VALUES $sendRow";

        $this->showResult($DB->queryTF($query));

        return $next();
    }


    private array $categories = [
        'comments',
        'news',
        'articles'
    ];

}