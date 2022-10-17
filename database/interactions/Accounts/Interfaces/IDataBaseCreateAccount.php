<?php

namespace Database\Interactions\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseCreateAccount
{
    public function createAccount(array $input): User;
}
