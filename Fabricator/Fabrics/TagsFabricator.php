<?php

class TagsFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $DB->queryTF("DELETE FROM tags");
        $DB->queryTF("ALTER TABLE tags AUTO_INCREMENT = 1");

        $sendTags = [];

        foreach($this->tags as $tag) {
            $sendTags[] = "('".$tag."')";
        }

        $sendRow = implode(",", $sendTags);

        $this->showResult($DB->queryTF("INSERT INTO tags (name) VALUES $sendRow"));

        return $next();
    }

    private array $tags = [
        'Слухи',
        'Разработка',
        'Техника',
        'Компьютеры'
    ];

}