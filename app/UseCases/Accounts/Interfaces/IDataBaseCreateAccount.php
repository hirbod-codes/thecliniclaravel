<?php

namespace App\UseCases\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseCreateAccount
{
    public function createAccount(array $input): User;
}
