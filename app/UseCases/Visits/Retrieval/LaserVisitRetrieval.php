<?php

namespace App\UseCases\Visits\Retrieval;

use App\Models\Auth\User;
use App\Models\Order\LaserOrder;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;

class LaserVisitRetrieval
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp, IDataBaseRetrieveLaserVisits $db): array
    {
        if (!in_array($sortByTimestamp, ['desc', 'asc'])) {
            throw new \InvalidArgumentException(
                '$sortByTimestamp variable must be one of the \'desc\' or \'asc\' values, \'' . $sortByTimestamp . '\' given.',
                500
            );
        }

        return $db->getVisitsByUser($targetUser, $sortByTimestamp);
    }

    public function getVisitsByOrder(LaserOrder $laserOrder, string $sortByTimestamp, IDataBaseRetrieveLaserVisits $db): array
    {
        if (!in_array($sortByTimestamp, ['desc', 'asc'])) {
            throw new \InvalidArgumentException(
                '$sortByTimestamp variable must be one of the \'desc\' or \'asc\' values, \'' . $sortByTimestamp . '\' given.',
                500
            );
        }

        return $db->getVisitsByOrder($laserOrder, $sortByTimestamp);
    }

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, IDataBaseRetrieveLaserVisits $db, int $lastVisitTimestamp = null): array
    {
        if (
            !in_array($sortByTimestamp, ['desc', 'asc']) ||
            !in_array($operator, ['<>', '=', '<=', '<', '>=', '>'])
        ) {
            throw new \InvalidArgumentException(
                '$sortByTimestamp variable must be one of the \'desc\' or \'asc\' values, \'' . $sortByTimestamp . '\' given.',
                500
            );
        }

        return $db->getVisitsByTimestamp($roleName, $operator, $timestamp, $sortByTimestamp, $count, $lastVisitTimestamp);
    }
}
