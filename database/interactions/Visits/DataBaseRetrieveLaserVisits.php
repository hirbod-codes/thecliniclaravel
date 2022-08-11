<?php

namespace Database\Interactions\Visits;

use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;

class DataBaseRetrieveLaserVisits implements IDataBaseRetrieveLaserVisits
{
    public function getVisitsByUser(DSUser $dsTargetUser, string $sortByTimestamp): DSLaserVisits
    {
        $laserVisits = DB::table(($laserVisit = new LaserVisit)->getTable())
            ->select($laserVisit->getTable() . '.' . $laserVisit->getKeyName())
            ->join(
                ($laserOrder = new LaserOrder)->getTable(),
                $laserOrder->getTable() . '.' . $laserOrder->getKeyName(),
                '=',
                $laserVisit->getTable() . '.' . $laserOrder->getForeignKey()
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $laserOrder->getTable() . '.' . $order->getForeignKey()
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $user->getForeignKey()
            )
            ->orderBy($laserVisit->getTable() . '.visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->get()
            ->all()
            //
        ;

        if (count($laserVisits) !== 0) {
            $query = LaserVisit::query();
            foreach ($laserVisits as $key => $value) {
                $query = $query->where($laserVisit->getKeyName(), '=', $value->{$laserVisit->getKeyName()}, 'or');
            }
            $laserVisits = $query->get()->all();

            $dsLaserVisits = LaserVisit::getDSLaserVisits($laserVisits, strtoupper($sortByTimestamp));
        } else {
            $dsLaserVisits = new DSLaserVisits();
        }

        return $dsLaserVisits;
    }

    public function getVisitsByOrder(DSUser $dsTargetUser, DSLaserOrder $dsLaserOrder, string $sortByTimestamp): DSLaserVisits
    {
        $laserVisits = DB::table(($laserVisit = new LaserVisit)->getTable())
            ->select($laserVisit->getTable() . '.' . $laserVisit->getKeyName())
            ->join(
                ($laserOrder = new LaserOrder)->getTable(),
                $laserOrder->getTable() . '.' . $laserOrder->getKeyName(),
                '=',
                $laserVisit->getTable() . '.' . $laserOrder->getForeignKey()
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $laserOrder->getTable() . '.' . $order->getForeignKey()
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $user->getForeignKey()
            )
            ->orderBy($laserVisit->getTable() . '.visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->where($laserVisit->getTable() . '.' . $laserOrder->getForeignKey(), '=', $dsLaserOrder->getId())
            ->get()
            ->all()
            //
        ;

        if (count($laserVisits) !== 0) {
            $query = LaserVisit::query();
            foreach ($laserVisits as $key => $value) {
                $query = $query->where($laserVisit->getKeyName(), '=', $value->{$laserVisit->getKeyName()}, 'or');
            }
            $laserVisits = $query->get()->all();

            $dsLaserVisits = LaserVisit::getDSLaserVisits($laserVisits, strtoupper($sortByTimestamp));
        } else {
            $dsLaserVisits = new DSLaserVisits();
        }

        return $dsLaserVisits;
    }

    public function getVisitsByTimestamp(string $operator, int $timestamp, string $sortByTimestamp): DSLaserVisits
    {
        $laserVisits = LaserVisit::query()
            ->orderBy('visit_timestamp', strtolower($sortByTimestamp))
            ->where('visit_timestamp', $operator, $timestamp)
            ->get()
            ->all()
            //
        ;

        $dsLaserVisits = LaserVisit::getDSLaserVisits($laserVisits, strtoupper($sortByTimestamp));

        return $dsLaserVisits;
    }
}
