<?php

namespace App\UseCases\Orders\Retrieval;

use App\Models\Auth\User;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;

class RegularOrderRetrieval
{
    private static array $operatorValues = ["<=", ">=", "=", "<>", "<", ">"];

    public function getRegularOrdersByPriceByUser(string $operator, int $price, User $targetUser, IDataBaseRetrieveRegularOrders $db): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getRegularOrdersByPriceByUser($operator, $price, $targetUser);
    }

    public function getRegularOrdersByPrice(string $roleName, int $count, string $operator, int $price, IDataBaseRetrieveRegularOrders $db, int $lastOrderId = null): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getRegularOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);
    }

    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser, IDataBaseRetrieveRegularOrders $db): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        };

        return $db->getRegularOrdersByTimeConsumptionByUser($operator, $timeConsumption, $targetUser);
    }

    public function getRegularOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, IDataBaseRetrieveRegularOrders $db, int $lastOrderId = null): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getRegularOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);
    }

    public function getRegularOrdersByUser(User $targetUser, IDataBaseRetrieveRegularOrders $db): array
    {
        return $db->getRegularOrdersByUser($targetUser);
    }

    public function getRegularOrders(string $roleName, int $count, IDataBaseRetrieveRegularOrders $db, int $lastOrderId = null): array
    {
        return $db->getRegularOrders($roleName, $count, $lastOrderId);
    }
}
