<?php

namespace App\UseCases\Accounts\Interfaces;

use App\Models\User;

interface IDataBaseRetrieveAccounts
{
    /**
     * @return \App\Models\User[]
     */
    public function getAccounts(int $count, string $ruleName, ?int $lastVisitId = null): array;

    public function getAccount(string $targetUserUsername): User;
}
