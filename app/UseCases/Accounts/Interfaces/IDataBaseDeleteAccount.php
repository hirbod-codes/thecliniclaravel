<?php

namespace App\UseCases\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseDeleteAccount
{
    public function deleteAccount(User $targetUser): void;
}
