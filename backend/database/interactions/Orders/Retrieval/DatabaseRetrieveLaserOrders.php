<?php

namespace Database\Interactions\Orders\Retrieval;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Helpers\TraitAuthResolver;
use App\Models\Order\LaserOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use Illuminate\Database\Eloquent\Builder;

class DatabaseRetrieveLaserOrders implements IDataBaseRetrieveLaserOrders
{
    use TraitAuthResolver;

    public function collectDSPartsFromNames(array $partsNames = [], string $gender): DSParts
    {
        if (count($partsNames) === 0) {
            return new DSParts($gender);
        }

        $query = Part::query();
        $first = true;
        foreach ($requestParts = $partsNames as $partName) {
            if ($first) {
                $first = false;
                $method = 'where';
            } else {
                $method = 'orWhere';
            }

            $query = $query->{$method}(function (Builder $query) use ($gender, $partName) {
                $query->where('gender', '=', $gender)->where('name', '=', $partName);
            });
        }
        $parts = $query->get()->all();

        return Part::getDSParts($parts, $gender);
    }


    public function collectDSPackagesFromNames(array $packagesNames = [], string $gender): DSPackages
    {
        if (count($packagesNames) === 0) {
            return new DSPackages($gender);
        }

        $query = Package::query();
        foreach ($requestPackages = $packagesNames as $packageName) {
            $query = $query->orWhere(function (Builder $query) use ($gender, $packageName) {
                $query->where('gender', '=', $gender)->where('name', '=', $packageName);
            });
        }
        $packages = $query->get()->all();

        return Package::getDSPackages($packages, $gender);
    }

    public function getLaserOrderById(int $id): LaserOrder
    {
        return LaserOrder::query()->whereKey($id)->firstOrFail();
    }

    /**
     * @param string $operator Must be one the followings: "<=" ">=" "=" "<>" "<" ">"
     * @param integer $price
     * @param \App\Models\User $targetUser
     * @return LaserOrder[]
     */
    public function getLaserOrdersByPriceByUser(string $operator, int $price, User $targetUser): array
    {
        $orders = LaserOrder::query()
            ->where('price', $operator, $price)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->with(['parts', 'packages'])
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
     * @return LaserOrder[]
     */
    public function getLaserOrdersByPrice(string $roleName, int $lastOrderId = null, int $count, string $operator, int $price): array
    {
        $query = LaserOrder::query();

        if ($lastOrderId) {
            $query->where((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), 'desc')
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
            ->with(['parts', 'packages'])
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
     * @return LaserOrder[]
     */
    public function getLaserOrdersByTimeConsumptionByUser(string $operator, int $timeConsumption, User $targetUser): array
    {
        $orders = LaserOrder::query()
            ->where('needed_time', $operator, $timeConsumption)
            ->whereHas('order', function ($query) use ($targetUser) {
                $query->whereHas('user', function (Builder $query) use ($targetUser) {
                    $query->whereKey($targetUser->getKey());
                });
            })
            ->with(['parts', 'packages'])
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
     * @return LaserOrder[]
     */
    public function getLaserOrdersByTimeConsumption(string $roleName, int $count, string $operator, int $timeConsumption, int $lastOrderId = null): array
    {
        $query = LaserOrder::query();

        if ($lastOrderId) {
            $query->where((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), '<', $lastOrderId);
        }

        $orders = $query
            ->orderBy((new LaserOrder)->getTable() . '.' . (new LaserOrder)->getKeyName(), 'desc')
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
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param User $targetUser
     * @return LaserOrder[]
     */
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
            ->all()
            //
        ;

        return $orders;
    }

    /**
     * @param string $roleName
     * @param integer $count
     * @param integer|null $lastOrderId
     * @return LaserOrder[]
     */
    public function getLaserOrders(string $roleName, int $count, int $lastOrderId = null): array
    {
        $laserOrder = new LaserOrder;
        $query = LaserOrder::query();

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
                            // $method = 'orWhereHas';
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
            ->with(['parts', 'packages'])
            ->take($count)
            ->get()
            ->all()
            //
        ;

        return $orders;
    }
}
