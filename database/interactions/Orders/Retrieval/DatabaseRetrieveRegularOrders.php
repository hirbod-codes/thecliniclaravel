<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use TheClinicDataStructures\DataStructures\User\DSUser;

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

        $orders = RegularOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->where('price', $operator, $price)
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
        $orders = RegularOrder::query()->orderBy((new RegularOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->where('price', $operator, $price)
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

        $orders = RegularOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->where('needed_time', $operator, $timeCosumption)
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
        $orders = RegularOrder::query()->orderBy((new RegularOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->where('needed_time', $operator, $timeCosumption)
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

        $orders = RegularOrder::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where($user->getForeignKey(), '=', $user->getKey())
                    //
                ;
            })
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getDSRegularOrders($orders);
    }

    public function getRegularOrders(int $count, int $lastOrderId = null): DSRegularOrders
    {
        $orders = RegularOrder::query()->orderBy((new RegularOrder)->getKeyName(), 'desc');

        if ($lastOrderId) {
            $orders = $orders->where((new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $orders
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return RegularOrder::getMixedDSRegularOrders($orders);
    }
}
