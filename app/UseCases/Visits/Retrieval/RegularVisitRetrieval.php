<?php

namespace App\UseCases\Visits\Retrieval;

use App\Models\Auth\User;
use App\Models\Order\RegularOrder;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;

class RegularVisitRetrieval
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp, IDataBaseRetrieveRegularVisits $db): array
    {
        if (!in_array($sortByTimestamp, ['desc', 'asc'])) {
            throw new \InvalidArgumentException(
                '$sortByTimestamp variable must be one of the \'desc\' or \'asc\' values, \'' . $sortByTimestamp . '\' given.',
                500
            );
        }

        return $db->getVisitsByUser($targetUser, $sortByTimestamp);
    }

    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp, IDataBaseRetrieveRegularVisits $db): array
    {
        if (!in_array($sortByTimestamp, ['desc', 'asc'])) {
            throw new \InvalidArgumentException(
                '$sortByTimestamp variable must be one of the \'desc\' or \'asc\' values, \'' . $sortByTimestamp . '\' given.',
                500
            );
        }

        return $db->getVisitsByOrder($regularOrder, $sortByTimestamp);
    }

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null, IDataBaseRetrieveRegularVisits $db): array
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
