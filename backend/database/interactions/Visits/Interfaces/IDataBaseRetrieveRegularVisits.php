<?php

namespace Database\Interactions\Visits\Interfaces;

use App\DataStructures\Visit\Regular\DSRegularVisits;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\RegularVisit;

interface IDataBaseRetrieveRegularVisits extends IDataBaseRetrieveVisits
{
    /**
     * @param User $targetUser
     * @param string $sortByTimestamp
     * @return RegularVisit[]
     */
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array;

    /**
     * @param RegularOrder $regularOrder
     * @param string $sortByTimestamp
     * @return RegularVisit[]
     */
    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp): array;

    /**
     * @param string $roleName
     * @param string $operator
     * @param integer $timestamp
     * @param string $sortByTimestamp
     * @param integer $count
     * @param integer|null $lastVisitTimestamp
     * @return RegularVisit[]
     */
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
