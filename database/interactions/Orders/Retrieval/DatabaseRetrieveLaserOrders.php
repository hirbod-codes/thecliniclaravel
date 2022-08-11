<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\User;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use TheClinicDataStructures\DataStructures\User\DSUser;

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

        $orders = LaserOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->where('price', $operator, $price)
            ->with(['parts', 'packages'])
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
        $orders = LaserOrder::query()->orderBy((new LaserOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->where('price', $operator, $price)
            ->with(['parts', 'packages'])
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

        $orders = LaserOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->where('needed_time', $operator, $timeCosumption)
            ->with(['parts', 'packages'])
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
        $orders = LaserOrder::query()->orderBy((new LaserOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->where('needed_time', $operator, $timeCosumption)
            ->with(['parts', 'packages'])
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

        $orders = LaserOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->with(['parts', 'packages'])
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getDSLaserOrders($orders);
    }

    public function getLaserOrders(int $count, int $lastOrderId = null): DSLaserOrders
    {
        $orders = LaserOrder::query()->orderBy((new LaserOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return LaserOrder::getMixedDSLaserOrders($orders);
    }
}
