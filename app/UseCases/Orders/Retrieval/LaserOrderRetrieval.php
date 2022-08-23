<?php

namespace App\UseCases\Orders\Retrieval;

use App\Models\Auth\User;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;

class LaserOrderRetrieval
{
    private static array $operatorValues = ["<=", ">=", "=", "<>", "<", ">"];

    public function getLaserOrdersByPriceByUser(string $operator, int $price, User $targetUser, IDataBaseRetrieveLaserOrders $db): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getLaserOrdersByPriceByUser($operator, $price, $targetUser);
    }

    public function getLaserOrdersByPrice(string $roleName, int $count, string $operator, int $price, IDataBaseRetrieveLaserOrders $db, int $lastOrderId = null): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getLaserOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);
    }

    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser, IDataBaseRetrieveLaserOrders $db): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getLaserOrdersByTimeConsumptionByUser($operator, $timeConsumption, $targetUser);
    }

    public function getLaserOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, IDataBaseRetrieveLaserOrders $db, int $lastOrderId = null): array
    {
        if (!in_array($operator, self::$operatorValues)) {
            throw new \RuntimeException("The operator parameter has an invalid value.", 500);
        }

        return $db->getLaserOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);
    }

    public function getLaserOrdersByUser(User $targetUser, IDataBaseRetrieveLaserOrders $db): array
    {
        return $db->getLaserOrdersByUser($targetUser);
    }

    public function getLaserOrders(string $roleName, int $count, IDataBaseRetrieveLaserOrders $db, int $lastOrderId = null): array
    {
        return $db->getLaserOrders($roleName, $count, $lastOrderId);
    }
}
