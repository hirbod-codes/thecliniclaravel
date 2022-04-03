<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\User;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrders;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;

class DatabaseRetrieveRegularOrders implements IDataBaseRetrieveRegularOrders
{
    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param DSUser $targetUser
     * @return DSRegularOrders
     */
    public function getRegularOrdersByPriceByUser(string $operator, int $price, DSUser $targetUser): DSRegularOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'regularOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->whereHas($relationName, function ($query) use ($operator, $price) {
                $query
                    ->where('price', $operator, $price)
                    //
                ;
            })
            ->with([$relationName])
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getDSRegularOrders($orders);
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param DSUser $targetUser
     * @return DSRegularOrders
     */
    public function getRegularOrdersByPrice(int $lastOrderId = null, int $count, string $operator, int $price): DSRegularOrders
    {
        $relationName = 'regularOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->whereHas($relationName, function ($query) use ($operator, $price) {
                $query
                    ->where('price', $operator, $price)
                    //
                ;
            })
            ->with([$relationName])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getMixedDSRegularOrders($orders);
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param DSUser $targetUser
     * @return DSRegularOrders
     */
    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeCosumption, DSUser $targetUser): DSRegularOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'regularOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->whereHas($relationName, function ($query) use ($operator, $timeCosumption) {
                $query
                    ->where('needed_time', $operator, $timeCosumption)
                    //
                ;
            })
            ->with([$relationName])
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getDSRegularOrders($orders);
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param DSUser $targetUser
     * @return DSRegularOrders
     */
    public function getRegularOrdersByTimeConsumption(int $count, string $operator, int $timeCosumption, int $lastOrderId = null): DSRegularOrders
    {
        $relationName = 'regularOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->whereHas($relationName, function ($query) use ($operator, $timeCosumption) {
                $query
                    ->where('needed_time', $operator, $timeCosumption)
                    //
                ;
            })
            ->with([$relationName])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getMixedDSRegularOrders($orders);
    }

    public function getRegularOrdersByUser(DSUser $targetUser): DSRegularOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'regularOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->with([$relationName])
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getDSRegularOrders($orders);
    }

    public function getRegularOrders(int $count, int $lastOrderId = null): DSRegularOrders
    {
        $relationName = 'regularOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->with([$relationName])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getMixedDSRegularOrders($orders);
    }
}
