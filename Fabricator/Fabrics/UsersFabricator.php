<?php

/* ФАБРИКА ПОЛЬЩОВАТЕЛЕЙ*/

class UsersFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $DB->queryTF('DELETE FROM users');
        $DB->queryTF('ALTER TABLE users AUTO_INCREMENT = 1');

        $faker = Faker\Factory::create();

        for($i = 0; $i < 1500; $i++) {
            $email = $faker->unique()->email();
            $name = $faker->unique()->userName();
            $password = password_hash($faker->text(15), PASSWORD_DEFAULT);
            $sex = rand(0, 1);
            array_push($this->users, ['email' => $email, 'name' => $name, 'password' => $password, 'sex' => $sex]);
        }


        $sendUsers = [];

        foreach($this->users as $user) {
            $sendUsers[] = "("."'".$user['email']."','".$user['name']."','".$user['password']."','".$user['sex']."')";
        } 

        $sendRow = implode(",", $sendUsers);


        $this->showResult($DB->queryTF("INSERT INTO users (email, name, password, sex) VALUES $sendRow"));

        return $next();
    }


    private array $users = [];
}