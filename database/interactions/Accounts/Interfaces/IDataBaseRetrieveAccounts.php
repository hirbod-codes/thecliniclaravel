<?php

namespace Database\Interactions\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseRetrieveAccounts
{
    public function getAccountsCount(string $roleName): int;

    /**
     * @return \App\Models\User[]
     */
    public function getAccounts(int $count, string $roleName, ?int $lastVisitId = null): array;

    public function getAccount(string $targetUserUsername): User;
}
