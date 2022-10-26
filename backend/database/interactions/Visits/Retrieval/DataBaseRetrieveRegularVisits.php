<?php

namespace Database\Interactions\Visits\Retrieval;

use App\Auth\CheckAuthentication;
use App\DataStructures\Visit\Regular\DSRegularVisits;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use Illuminate\Database\Eloquent\Builder;

class DataBaseRetrieveRegularVisits implements IDataBaseRetrieveRegularVisits
{
    private CheckAuthentication $checkAuthentication;

    public function __construct(CheckAuthentication $checkAuthentication = null)
    {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }


    /**
     * @param User $targetUser
     * @param string $sortByTimestamp
     * @return RegularVisit[]
     */
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
            ->all()
            //
        ;

        return $regularVisits;
    }

    /**
     * @param RegularOrder $regularOrder
     * @param string $sortByTimestamp
     * @return RegularVisit[]
     */
    public function getVisitsByOrder(RegularOrder $regularOrder, string $sortByTimestamp): array
    {
        $regularVisits = RegularVisit::query()
            ->whereHas('regularOrder', function (Builder $query) use ($regularOrder) {
                $query->whereKey($regularOrder->getKey());
            })
            ->get()
            ->all()
            //
        ;

        return $regularVisits;
    }

    /**
     * @param string $roleName
     * @param string $operator
     * @param integer $timestamp
     * @param string $sortByTimestamp
     * @param integer $count
     * @param integer|null $lastVisitTimestamp
     * @return RegularVisit[]
     */
    public function getVisitsByTimestamp(string $roleName, string $operator, int $timestamp, string $sortByTimestamp, int $count, int $lastVisitTimestamp = null): array
    {
        $user = $this->checkAuthentication->getAuthenticated();
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
            ->all()
            //
        ;

        return $regularVisits;
    }

    /**
     * @param string $sort 'asc' or 'desc'
     * @param string $operator >=, <>, <, <=, ...
     * @param \DateTime $now
     * @return RegularVisit[]
     */
    public function getFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): array
    {
        return RegularVisit::query()
            ->orderBy('visit_timestamp', $sort)
            ->where('visit_timestamp', $operator, $now)
            ->get()
            ->all()
            //
        ;
    }

    public function getDSFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): DSRegularVisits
    {
        return RegularVisit::getDSRegularVisits($this->getFutureVisits($sort, $operator, $now), 'ASC');
    }

    public function getRegularVisitById(int $visitId): RegularVisit
    {
        return RegularVisit::query()->whereKey($visitId)->firstOrFail();
    }
}
