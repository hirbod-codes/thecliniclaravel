<?php

namespace Database\Interactions\Visits;

use App\Auth\CheckAuthentication;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use Illuminate\Database\Eloquent\Builder;

class DataBaseRetrieveLaserVisits implements IDataBaseRetrieveLaserVisits
{
    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array
    {
        $laserVisits = LaserVisit::query()
            ->whereHas('laserOrder', function (Builder $query) use ($targetUser) {
                $query->whereHas('order', function (Builder $query) use ($targetUser) {
                    $query->whereHas('user', function (Builder $query) use ($targetUser) {
                        $query->whereKey($targetUser->getKey());
                    });
                });
            })
            ->get()
            ->toArray()
            //
        ;

        return $laserVisits;
    }

    public function getVisitsByOrder(LaserOrder $laserOrder, string $sortByTimestamp): array
    {
        $laserVisits = LaserVisit::query()
            ->whereHas('laserOrder', function (Builder $query) use ($laserOrder) {
                $query->whereKey($laserOrder->getKey());
            })
            ->get()
            ->toArray()
            //
        ;

        return $laserVisits;
    }

    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array
    {
        $user = (new CheckAuthentication)->getAuthenticated();
        $userRoleName = $user->authenticatableRole->role->roleName->name;
        $canReadSelf = false;
        foreach ($user->authenticatableRole->role->role->retrieveVisitSubjects as $retrieveVisit) {
            if ($retrieveVisit->object !== null) {
                continue;
            }
            $canReadSelf = true;
            break;
        }
        $isSelf = $userRoleName === $roleName;

        /** @var Builder $query */
        $query = LaserVisit::query()
            ->orderBy('visit_timestamp', strtolower($sortByTimestamp))
            ->where('visit_timestamp', $operator, $timestamp)
            //
        ;

        if ($lastVisitTimestamp) {
            $query->where('visit_timestamp', strtolower($sortByTimestamp) === 'asc' ? '>' : '<', $lastVisitTimestamp);
        }

        $query
            ->whereHas('laserOrder', function (Builder $query) use ($roleName, $isSelf, $canReadSelf, $user) {
                $query->whereHas('order', function (Builder $query) use ($roleName, $isSelf, $canReadSelf, $user) {
                    $query->whereHas('user', function (Builder $query) use ($roleName, $isSelf, $canReadSelf, $user) {
                        if ($isSelf && !$canReadSelf) {
                            $query->whereKeyNot($user->getKey());
                        }
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
                });
            })
            //
        ;

        $laserVisits = $query
            ->take($count)
            ->get()
            ->toArray()
            //
        ;

        return $laserVisits;
    }
}
