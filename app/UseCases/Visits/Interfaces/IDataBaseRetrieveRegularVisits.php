<?php

namespace App\UseCases\Visits\Interfaces;

use App\Models\Order\RegularOrder;
use App\Models\User;

interface IDataBaseRetrieveRegularVisits extends IDataBaseRetrieveVisits
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array;

    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp): array;

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array;
}
