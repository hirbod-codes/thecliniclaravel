<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\LaserOrder;
use App\Models\User;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use Illuminate\Database\Eloquent\Builder;

class DatabaseRetrieveLaserOrders implements IDataBaseRetrieveLaserOrders
{
    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\User $targetUser
     * @return array
     */
    public function getLaserOrdersByPriceByUser(string $operator, int $price, User $targetUser): array
    {
        $orders = LaserOrder::query()
            ->where('needed_time', $operator, $price)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->with(['parts', 'packages'])
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param integer $lastOrderId
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\DataStructures\User\DSUser $targetUser
     * @return array
     */
    public function getLaserOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array
    {
        $query = LaserOrder::query();

        if ($lastOrderId) {
            $query->where((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), 'desc')
            ->where('needed_time', $operator, $price)
            ->whereHas('order', function ($query) use ($roleName) {
                $query->whereHas('user', function (Builder $query) use ($roleName) {
                    $i = 0;
                    foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                        if ($i === 0) {
                            $query->whereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        } else {
                            $query->orWhereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        }
                        $i++;
                    }
                });
            })
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $timeCosumption
     * @param \App\Models\User $targetUser
     * @return array
     */
    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeCosumption, User $targetUser): array
    {
        $orders = LaserOrder::query()
            ->where('needed_time', $operator, $timeCosumption)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->with(['parts', 'packages'])
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param integer $lastOrderId
     * @return array
     */
    public function getLaserOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeCosumption, int $lastOrderId = null): array
    {
        $query = LaserOrder::query();

        if ($lastOrderId) {
            $query->where((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), 'desc')
            ->where('needed_time', $operator, $timeCosumption)
            ->whereHas('order', function ($query) use ($roleName) {
                $query->whereHas('user', function (Builder $query) use ($roleName) {
                    $i = 0;
                    foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                        if ($i === 0) {
                            $query->whereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        } else {
                            $query->orWhereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        }
                        $i++;
                    }
                });
            })
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }

    public function getLaserOrdersByUser(User $targetUser): array
    {
        $orders = LaserOrder::query()
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->with(['parts', 'packages'])
            ->get()
            //
        ;

        return $orders->toArray();
    }

    public function getLaserOrders(string $roleName, int $count, int $lastOrderId = null): array
    {
        $query = LaserOrder::query();

        if ($lastOrderId) {
            $query->where((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), 'desc')
            ->whereHas('order', function ($query) use ($roleName) {
                $query->whereHas('user', function (Builder $query) use ($roleName) {
                    $i = 0;
                    foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                        if ($i === 0) {
                            $query->whereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        } else {
                            $query->orWhereHas($relation, function (Builder $query) use ($roleName) {
                                $query->whereHas('role', function (Builder $query) use ($roleName) {
                                    $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                        $query->where('name', '=', $roleName);
                                    });
                                });
                            });
                        }
                        $i++;
                    }
                });
            })
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }
}
