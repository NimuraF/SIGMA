<?php

/* ФАБРИКА РОЛЕЙ */

class RolesFabricator extends BaseFabricator implements IFabricate {

    public function fabricate(callable $next)
    {
        $DB = new DB();

        $DB->queryTF('DELETE from roles');
        $DB->queryTF('ALTER TABLE roles AUTO_INCREMENT = 1');

        $sendRoles = [];

        foreach ($this->roles as $role) {
            $sendRoles[] = "('".$role."')";
        }

        
        $sendRow = implode(',', $sendRoles);

        $query = "INSERT INTO roles (name) VALUES $sendRow";
        

        $this->showResult($DB->queryTF($query));
    

        return $next();
    }

    private array $roles = [
        'Admin',
        'User',
        'Moderator'
    ];

}