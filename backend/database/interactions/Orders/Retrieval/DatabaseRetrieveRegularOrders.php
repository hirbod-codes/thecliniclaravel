<?php

namespace Database\Interactions\Orders\Retrieval;

use App\Helpers\TraitAuthResolver;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Illuminate\Database\Eloquent\Builder;

class DatabaseRetrieveRegularOrders implements IDataBaseRetrieveRegularOrders
{
    use TraitAuthResolver;

    public function getRegularOrderById(int $id): RegularOrder
    {
        return RegularOrder::query()->whereKey($id)->firstOrFail();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByPriceByUser(string $operator, int $price, User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->where('price', $operator, $price)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param integer $lastOrderId
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\DataStructures\User\DSUser $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array
    {
        $query = RegularOrder::query();

        if ($lastOrderId) {
            $query->where((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), 'desc')
            ->where('price', $operator, $price)
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
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $timeConsumption
     * @param \App\Models\User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->where('needed_time', $operator, $timeConsumption)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param integer $count
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param integer $lastOrderId
     * @return RegularOrder[]
     */
    public function getRegularOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, int $lastOrderId = null): array
    {
        $query = RegularOrder::query();

        if ($lastOrderId) {
            $query->where((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new RegularOrder)->getTable() . '.' . (new RegularOrder)->getKeyName(), 'desc')
            ->where('needed_time', $operator, $timeConsumption)
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
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param User $targetUser
     * @return RegularOrder[]
     */
    public function getRegularOrdersByUser(User $targetUser): array
    {
        $orders = RegularOrder::query()
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->get()
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param string $roleName
     * @param integer $count
     * @param integer|null $lastOrderId
     * @return RegularOrder[]
     */
    public function getRegularOrders(string $roleName, int $count, int $lastOrderId = null): array
    {
        $laserOrder = new RegularOrder;
        $query = RegularOrder::query();

        if (!is_null($lastOrderId) && $lastOrderId > 0) {
            $query->where($laserOrder->getTable() . '.' . $laserOrder->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy($laserOrder->getTable() . '.' . $laserOrder->getKeyName(), 'desc')
            ->whereHas('order', function ($query) use ($roleName) {
                $query->whereHas('user', function (Builder $query) use ($roleName) {
                    $first = true;
                    foreach ($this->authModelsClassName() as $className) {
                        if ($first) {
                            $first = false;
                            $method = 'whereHas';
                        } else {
                            $method = 'orWhereHas';
                        }

                        $query->{$method}('childModel' . $className, function (Builder $query) use ($roleName) {
                            $query->whereHas('role', function (Builder $query) use ($roleName) {
                                $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                    $query->where('name', '=', $roleName);
                                });
                            });
                        });
                    }
                });
            })
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return $orders;
    }
}
