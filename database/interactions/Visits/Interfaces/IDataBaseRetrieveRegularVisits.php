<?php

namespace Database\Interactions\Visits\Interfaces;

use App\DataStructures\Visit\Regular\DSRegularVisits;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\RegularVisit;

interface IDataBaseRetrieveRegularVisits extends IDataBaseRetrieveVisits
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array;

    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp): array;

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array;

    /**
     * @param string $sort 'asc' or 'desc'
     * @param string $operator >=, <>, <, <=, ...
     * @param \DateTime $now
     * @return RegularVisit[]
     */
    public function getFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): array;

    public function getDSFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): DSRegularVisits;

    public function getRegularVisitById(int $visitId): RegularVisit;
}
