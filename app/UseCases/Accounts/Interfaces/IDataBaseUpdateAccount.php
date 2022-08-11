<?php

namespace App\UseCases\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseUpdateAccount
{
    public function massUpdateAccount(array $input, User $targetUser): User;
    public function updateAccount(string $attribute, mixed $newValue, User $targetUser): User;
}
