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
     * @param \App\Models\User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByPriceByUser(string $operator, int $price, User $targetUser): array;

    /**
     * @param integer $lastOrderId
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\DataStructures\User\DSUser $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array;

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $timeConsumption
     * @param \App\Models\User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser): array;

    /**
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param integer $lastOrderId
     * @return RegularOrder[]
     */
    public function getRegularOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, int $lastOrderId = null): array;

    /**
     * @param User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByUser(User $targetUser): array;

    /**
     * @param string $roleName
     * @param integer $count
     * @param integer|null $lastOrderId
     * @return RegularOrder[]
     */
    public function getRegularOrders(string $roleName, int $count, int $lastOrderId = null): array;
}
