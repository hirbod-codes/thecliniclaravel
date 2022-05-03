<?php

namespace Database\Interactions\Visits;

use App\Models\Order\RegularOrder;
use App\Models\Order\Order;
use App\Models\User;
use App\Models\Visit\RegularVisit;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;

class DataBaseRetrieveRegularVisits implements IDataBaseRetrieveRegularVisits
{
    public function getVisitsByUser(DSUser $dsTargetUser, string $sortByTimestamp): DSRegularVisits
    {
        $regularVisits = DB::table(($regularVisit = new RegularVisit)->getTable())
            ->select($regularVisit->getTable() . '.' . $regularVisit->getKeyName())
            ->join(
                ($regularOrder = new RegularOrder)->getTable(),
                $regularOrder->getTable() . '.' . $regularOrder->getKeyName(),
                '=',
                $regularVisit->getTable() . '.' . $regularOrder->getForeignKey()
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $regularOrder->getTable() . '.' . $order->getForeignKey()
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $user->getForeignKey()
            )
            ->orderBy($regularVisit->getTable() . '.visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->get()
            ->all()
            //
        ;

        if (count($regularVisits) !== 0) {
            $query = RegularVisit::query();
            foreach ($regularVisits as $key => $value) {
                $query = $query->where($regularVisit->getKeyName(), '=', $value->{$regularVisit->getKeyName()}, 'or');
            }
            $regularVisits = $query->get()->all();

            $dsRegularVisits = RegularVisit::getDSRegularVisits($regularVisits, strtoupper($sortByTimestamp));
        } else {
            $dsRegularVisits = new DSRegularVisits();
        }

        return $dsRegularVisits;
    }

    public function getVisitsByOrder(DSUser $dsTargetUser, DSRegularOrder $dsRegularOrder, string $sortByTimestamp): DSRegularVisits
    {
        $regularVisits = DB::table(($regularVisit = new RegularVisit)->getTable())
            ->select($regularVisit->getTable() . '.' . $regularVisit->getKeyName())
            ->join(
                ($regularOrder = new RegularOrder)->getTable(),
                $regularOrder->getTable() . '.' . $regularOrder->getKeyName(),
                '=',
                $regularVisit->getTable() . '.' . $regularOrder->getForeignKey()
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $regularOrder->getTable() . '.' . $order->getForeignKey()
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $user->getForeignKey()
            )
            ->orderBy($regularVisit->getTable() . '.visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->where($regularVisit->getTable() . '.' . $regularOrder->getForeignKey(), '=', $dsRegularOrder->getId())
            ->get()
            ->all()
            //
        ;

        if (count($regularVisits) !== 0) {
            $query = RegularVisit::query();
            foreach ($regularVisits as $key => $value) {
                $query = $query->where($regularVisit->getKeyName(), '=', $value->{$regularVisit->getKeyName()}, 'or');
            }
            $regularVisits = $query->get()->all();

            $dsRegularVisits = RegularVisit::getDSRegularVisits($regularVisits, strtoupper($sortByTimestamp));
        } else {
            $dsRegularVisits = new DSRegularVisits();
        }

        return $dsRegularVisits;
    }

    public function getVisitsByTimestamp(string $operator, int $timestamp, string $sortByTimestamp): DSRegularVisits
    {
        $regularVisits = RegularVisit::query()
            ->orderBy('visit_timestamp', strtolower($sortByTimestamp))
            ->where('visit_timestamp', $operator, $timestamp)
            ->get()
            ->all()
            //
        ;

        $dsRegularVisits = RegularVisit::getDSRegularVisits($regularVisits, strtoupper($sortByTimestamp));

        return $dsRegularVisits;
    }
}
