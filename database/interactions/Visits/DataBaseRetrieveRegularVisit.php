<?php

namespace Database\Interactions\Visits;

use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
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
            ->join(
                ($RegularOrder = new RegularOrder)->getTable(),
                $RegularOrder->getTable() . '.' . $RegularOrder->getKeyName(),
                '=',
                $regularVisit->getTable() . '.' . $regularVisit->{$RegularOrder->getForeignKey()}
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $RegularOrder->getTable() . '.' . $RegularOrder->{$order->getForeignKey()}
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $order->{$user->getForeignKey()}
            )
            ->orderBy($regularVisit->getTable() . 'visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->get()
            ->all()
            //
        ;

        $dsRegularVisits = regularVisit::getDSRegularVisits($regularVisits, strtoupper($sortByTimestamp));

        return $dsRegularVisits;
    }

    public function getVisitsByOrder(DSUser $dsTargetUser, DSRegularOrder $dsRegularOrder, string $sortByTimestamp): DSRegularVisits
    {
        $regularVisits = DB::table(($regularVisit = new RegularVisit)->getTable())
            ->join(
                ($RegularOrder = new RegularOrder)->getTable(),
                $RegularOrder->getTable() . '.' . $RegularOrder->getKeyName(),
                '=',
                $regularVisit->getTable() . '.' . $regularVisit->{$RegularOrder->getForeignKey()}
            )
            ->join(
                ($order = new Order)->getTable(),
                $order->getTable() . '.' . $order->getKeyName(),
                '=',
                $RegularOrder->getTable() . '.' . $RegularOrder->{$order->getForeignKey()}
            )
            ->join(
                ($user = new User)->getTable(),
                $user->getTable() . '.' . $user->getKeyName(),
                '=',
                $order->getTable() . '.' . $order->{$user->getForeignKey()}
            )
            ->orderBy($regularVisit->getTable() . 'visit_timestamp', strtolower($sortByTimestamp))
            ->where($user->getTable() . '.' . $user->getKeyName(), '=', $dsTargetUser->getId())
            ->where($order->getTable() . '.' . $order->getKeyName(), '=', $dsRegularOrder->getId())
            ->get()
            ->all()
            //
        ;

        $dsRegularVisits = RegularVisit::getDSRegularVisits($regularVisits, strtoupper($sortByTimestamp));

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
