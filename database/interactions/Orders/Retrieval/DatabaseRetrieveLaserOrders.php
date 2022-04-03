<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\User;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;

class DatabaseRetrieveLaserOrders implements IDataBaseRetrieveLaserOrders
{
    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \TheClinicDataStructures\DataStructures\User\DSUser $targetUser
     * @return \TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders
     */
    public function getLaserOrdersByPriceByUser(string $operator, int $price, DSUser $targetUser): DSLaserOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'laserOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->whereHas($relationName, function ($query) use ($operator, $price) {
                $query
                    ->where('price', $operator, $price)
                    //
                ;
            })
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getDSLaserOrders($orders);
    }

    /**
     * @param integer $lastOrderId
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \TheClinicDataStructures\DataStructures\User\DSUser $targetUser
     * @return \TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders
     */
    public function getLaserOrdersByPrice(int $lastOrderId = null, int $count, string $operator, int $price): DSLaserOrders
    {
        $relationName = 'laserOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->whereHas($relationName, function ($query) use ($operator, $price) {
                $query
                    ->where('price', $operator, $price)
                    //
                ;
            })
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getMixedDSLaserOrders($orders);
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $timeCosumption
     * @param \TheClinicDataStructures\DataStructures\User\DSUser $targetUser
     * @return \TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders
     */
    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeCosumption, DSUser $targetUser): DSLaserOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'laserOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->whereHas($relationName, function ($query) use ($operator, $timeCosumption) {
                $query
                    ->where('needed_time', $operator, $timeCosumption)
                    //
                ;
            })
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getDSLaserOrders($orders);
    }

    /**
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \TheClinicDataStructures\DataStructures\User\DSUser $targetUser
     * @param integer $lastOrderId
     * @return \TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders
     */
    public function getLaserOrdersByTimeConsumption(int $count, string $operator, int $timeCosumption, int $lastOrderId = null): DSLaserOrders
    {
        $relationName = 'laserOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->whereHas($relationName, function ($query) use ($operator, $timeCosumption) {
                $query
                    ->where('needed_time', $operator, $timeCosumption)
                    //
                ;
            })
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getMixedDSLaserOrders($orders);
    }

    public function getLaserOrdersByUser(DSUser $targetUser): DSLaserOrders
    {
        /** @var User $user */
        $user = User::query()->where('username', '=', $targetUser->getUsername())->first();
        $userPrimaryKey = $user->{$user->getKeyName()};

        $relationName = 'laserOrder';
        $orders = Order::query()
            ->where($user->getForeignKey(), '=', $userPrimaryKey)
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getDSLaserOrders($orders);
    }

    public function getLaserOrders(int $count, int $lastOrderId = null): DSLaserOrders
    {
        $relationName = 'laserOrder';
        $orders = Order::query()
            ->orderBy((new Order)->getKeyName(), 'desc')
            ->where((new Order)->getKeyName(), '>', $lastOrderId)
            ->with([$relationName . '.parts', $relationName . '.packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getMixedDSLaserOrders($orders);
    }
}
