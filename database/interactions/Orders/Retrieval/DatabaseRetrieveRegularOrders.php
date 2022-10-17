<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Illuminate\Database\Eloquent\Builder;

class DatabaseRetrieveRegularOrders implements IDataBaseRetrieveRegularOrders
{
    public function getRegularOrderById(int $id): RegularOrder
    {
        return RegularOrder::query()->whereKey($id)->firstOrFail();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\User $targetUser
     * @return array
     */
    public function getRegularOrdersByPriceByUser(string $operator, int $price, User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->where('needed_time', $operator, $price)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @return array
     */
    public function getRegularOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array
    {
        $query = RegularOrder::query();

        if ($lastOrderId) {
            $query->where((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), 'desc')
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
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\User $targetUser
     * @return array
     */
    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeCosumption, User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->where('needed_time', $operator, $timeCosumption)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            //
        ;

        return $orders->toArray();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param User $targetUser
     * @return array
     */
    public function getRegularOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeCosumption, int $lastOrderId = null): array
    {
        $query = RegularOrder::query();

        if ($lastOrderId) {
            $query->where((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), 'desc')
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
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }

    public function getRegularOrdersByUser(User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            //
        ;

        return $orders->toArray();
    }

    public function getRegularOrders(string $roleName, int $count, int $lastOrderId = null): array
    {
        $query = RegularOrder::query();

        if ($lastOrderId) {
            $query->where((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), 'desc')
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
            ->take($count)
            ->get()
            //
        ;

        return $orders->toArray();
    }
}
