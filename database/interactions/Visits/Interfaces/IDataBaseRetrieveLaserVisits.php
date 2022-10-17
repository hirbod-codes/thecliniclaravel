<?php

namespace Database\Interactions\Visits\Interfaces;

use App\DataStructures\Visit\Laser\DSLaserVisits;
use App\Models\Order\LaserOrder;
use App\Models\User;
use App\Models\Visit\LaserVisit;

interface IDataBaseRetrieveLaserVisits extends IDataBaseRetrieveVisits
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array;

    public function getVisitsByOrder(LaserOrder $laserOrder, string $sortByTimestamp): array;

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array;

    /**
     * @param string $sort 'asc' or 'desc'
     * @param string $operator >=, <>, <, <=, ...
     * @param \DateTime $now
     * @return LaserVisit[]
     */
    public function getFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): array;

    public function getDSFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): DSLaserVisits;

    public function getLaserVisitById(int $visitId): LaserVisit;
}
