<?php

namespace App\UseCases\Orders\Interfaces;

use App\Models\User;

interface IDataBaseRetrieveLaserOrders
{
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
    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeCosumption, User $targetUser): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getLaserOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeCosumption, int $lastOrderId = null): array;

    public function getLaserOrdersByUser(User $targetUser): array;

    public function getLaserOrders(string $roleName, int $count, int $lastOrderId = null): array;
}
