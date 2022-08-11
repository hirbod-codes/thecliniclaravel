<?php

namespace App\UseCases\Visits\Interfaces;

use App\Models\Order\LaserOrder;
use App\Models\User;

interface IDataBaseRetrieveLaserVisits extends IDataBaseRetrieveVisits
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array;

    public function getVisitsByOrder(LaserOrder $laserOrder, string $sortByTimestamp): array;

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array;
}
