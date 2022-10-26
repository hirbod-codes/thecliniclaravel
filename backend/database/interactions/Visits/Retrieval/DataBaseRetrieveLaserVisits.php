<?php

namespace Database\Interactions\Visits\Retrieval;

use App\Auth\CheckAuthentication;
use App\DataStructures\Visit\Laser\DSLaserVisits;
use App\Models\Order\LaserOrder;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use Illuminate\Database\Eloquent\Builder;

class DataBaseRetrieveLaserVisits implements IDataBaseRetrieveLaserVisits
{
    private CheckAuthentication $checkAuthentication;

    public function __construct(CheckAuthentication $checkAuthentication = null)
    {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }

    /**
     * @param User $targetUser
     * @param string $sortByTimestamp
     * @return LaserVisit[]
     */
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
            ->all()
            //
        ;

        return $laserVisits;
    }

    /**
     * @param LaserOrder $laserOrder
     * @param string $sortByTimestamp
     * @return LaserVisit[]
     */
    public function getVisitsByOrder(LaserOrder $laserOrder, string $sortByTimestamp): array
    {
        $laserVisits = LaserVisit::query()
            ->whereHas('laserOrder', function (Builder $query) use ($laserOrder) {
                $query->whereKey($laserOrder->getKey());
            })
            ->get()
            ->all()
            //
        ;

        return $laserVisits;
    }

    /**
     * @param string $roleName
     * @param string $operator
     * @param integer $timestamp
     * @param string $sortByTimestamp
     * @param integer $count
     * @param integer|null $lastVisitTimestamp
     * @return LaserVisit[]
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
            ->all()
            //
        ;

        return $laserVisits;
    }

    /**
     * @param string $sort 'asc' or 'desc'
     * @param string $operator >=, <>, <, <=, ...
     * @param \DateTime $now
     * @return LaserVisit[]
     */
    public function getFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): array
    {
        return LaserVisit::query()
            ->orderBy('visit_timestamp', $sort)
            ->where('visit_timestamp', $operator, $now)
            ->get()
            ->all()
            //
        ;
    }

    public function getDSFutureVisits(string $sort = 'asc', string $operator = '>=', \DateTime $now = new \DateTime()): DSLaserVisits
    {
        $dsLaserVisits = new DSLaserVisits();

        foreach ($this->getFutureVisits() as $visit) {
            $dsLaserVisits[] = $visit->getDSLaserVisit();
        }

        return $dsLaserVisits;
    }

    public function getLaserVisitById(int $visitId): LaserVisit
    {
        return LaserVisit::query()->whereKey($visitId)->firstOrFail();
    }
}
