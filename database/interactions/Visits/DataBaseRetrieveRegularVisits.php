<?php

namespace Database\Interactions\Visits;

use App\Auth\CheckAuthentication;
use App\Models\Order\RegularOrder;
use App\Models\Traits\TraitDSDateTimePeriod;
use App\Models\Traits\TraitDSWeekDaysPeriods;
use App\Models\User;
use App\Models\Visit\RegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use Illuminate\Database\Eloquent\Builder;

class DataBaseRetrieveRegularVisits implements IDataBaseRetrieveRegularVisits
{
    use
        TraitDSWeekDaysPeriods,
        TraitDSDateTimePeriod;

    public function getVisitsByUser(User $targetUser, string $sortByTimestamp): array
    {
        $regularVisits = RegularVisit::query()
            ->whereHas('regularOrder', function (Builder $query) use ($targetUser) {
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

        return $regularVisits;
    }

    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp): array
    {
        $regularVisits = RegularVisit::query()
            ->whereHas('regularOrder', function (Builder $query) use ($regularOrder) {
                $query->whereKey($regularOrder->getKey());
            })
            ->get()
            ->toArray()
            //
        ;

        return $regularVisits;
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

        $query = RegularVisit::query()
            ->orderBy('visit_timestamp', strtolower($sortByTimestamp))
            ->where('visit_timestamp', $operator, $timestamp)
            //
        ;

        if ($lastVisitTimestamp) {
            $query->where('visit_timestamp', strtolower($sortByTimestamp) === 'asc' ? '>' : '<', $lastVisitTimestamp);
        }

        $query
            ->whereHas('regularOrder', function (Builder $query) use ($roleName, $isSelf, $canReadSelf, $user) {
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

        $regularVisits = $query
            ->take($count)
            ->get()
            ->toArray()
            //
        ;

        return $regularVisits;
    }
}
