<?php

class ArticlesFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $DB->queryTF("DELETE FROM articles");
        $DB->queryTF("ALTER TABLE articles AUTO_INCREMENT = 1");

        $users = $DB->select('users')->get();

        $faker = Faker\Factory::create("ru_RU");

        $sendArticles = [];

        for ($i = 0; $i < 1000; $i++) {
            $user = $users[array_rand($users)]['name'];

            $article_head = $faker->text(100);

            while(in_array($article_head, $this->heads) || str_contains($article_head, "'")) {
                $article_head = $faker->text(100);
            }

            $article_body = $faker->realText();

            while(str_contains($article_body, "'")) {
                $article_body = $faker->realText();
            }
            

            $sendArticles[] = "('".$user."','".$article_head."','".$article_body."')";
        }

        $sendRow = implode(",", $sendArticles);

        $this->showResult($DB->queryTF("INSERT INTO articles (author_name, article_head, article_body) VALUES $sendRow"));

        return $next();

    }

    /* Массив заголовков */
    private array $heads = [];

}