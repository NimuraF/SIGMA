<?php

class UsersRolesFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $users = $DB->select('users')->get();

        $usersRoles = [];

        foreach($users as $user) {
            $usersRoles[] = "('".$user['id']."','User')";
        }

        $sendRow = implode(',', $usersRoles);

        $this->showResult($DB->queryTF("INSERT INTO roles_users (user_id, role_name) VALUES $sendRow"));

        return $next();

    }

}