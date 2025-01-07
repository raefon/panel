<?php

namespace App\Models;

use Vizir\KeycloakWebGuard\Models\KeycloakUser;

class User extends KeycloakUser
{
    /**
     * Get the primary key name for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'custom_primary_key_name';
    }
}
