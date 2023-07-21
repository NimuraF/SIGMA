<?php

class UsersCommentsFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $users = $DB->select('users')->get();
        $categories = $DB->select('categories_for_comments')->get();

        $allEntities = [];

        foreach($categories as $category) {
            $allEntities[$category['category_name']] = $DB->select($category['category_name'], ['id'])->get();
        }

        $faker = Faker\Factory::create("ru_RU");

        for ($i = 0; $i < 6000; $i++) {
            $user = $users[array_rand($users, 1)]['name'];
            $category = $categories[array_rand($categories, 1)]['category_name'];
            $entity_id = array_rand($allEntities[$category], 1)['id'];
        }
    }

}