<?php

namespace Database\Interactions\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseUpdateAccount
{
    public function massUpdateAccount(array $userAattributes, array $userRoleAattributes, User $user): User;
}
