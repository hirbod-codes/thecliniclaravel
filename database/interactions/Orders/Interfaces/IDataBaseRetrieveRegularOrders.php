<?php

namespace Database\Interactions\Orders\Interfaces;

use App\Models\Order\RegularOrder;
use App\Models\User;

interface IDataBaseRetrieveRegularOrders
{
    public function getRegularOrderById(int $id): RegularOrder;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getRegularOrdersByPriceByUser(string $operator, int $price, User $targetUser): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getRegularOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\Auth\User $targetUser
     * @return array
     */
    public function getRegularOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, int $lastOrderId = null): array;

    public function getRegularOrdersByUser(User $targetUser): array;

    public function getRegularOrders(string $roleName, int $count, int $lastOrderId = null): array;
}
