<?php

namespace Database\Interactions\Accounts\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface IDataBaseRetrieveAccounts
{
    public function getAccountsCount(string $roleName): int;

    /**
     * @return Collection<int, App\Models\Auth\User>
     */
    public function getAccounts(int $count, string $roleName, ?int $lastAccountId = null): Collection;

    public function getAccount(string $targetUserUsername): User;
}
