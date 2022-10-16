<?php

namespace App\UseCases\Orders\Interfaces;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Models\Order\LaserOrder;
use App\Models\User;

interface IDataBaseRetrieveLaserOrders
{
    public function collectDSPacakgesFromNames(array $packagesNames = [], string $gender): DSPackages;

    public function collectDSPartsFromNames(array $partsNames = [], string $gender): DSParts;

    public function getLaserOrderById(int $id): LaserOrder;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getLaserOrdersByPriceByUser(string $operator, int $price, User $targetUser): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getLaserOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getLaserOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, int $lastOrderId = null): array;

    public function getLaserOrdersByUser(User $targetUser): array;

    public function getLaserOrders(string $roleName, int $count, int $lastOrderId = null): array;
}
