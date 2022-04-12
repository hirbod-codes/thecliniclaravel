<?php

namespace Database\Interactions\Visits;

use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\RegularVisit;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinic\Visit\IFindVisit;
use TheClinic\Visit\WeeklyVisit;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;

class DataBaseCreateRegularVisit implements IDataBaseCreateRegularVisit
{
    public function createRegularVisit(DSRegularOrder $dsRegularOrder, DSUser $dsTargetUser, IFindVisit $iFindVisit): DSRegularVisit
    {
        $founrd = false;
        foreach (User::query()->whereKey($dsTargetUser->getId())->first()->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null && $regularOrder->getKey() === $dsRegularOrder->getId()) {
                $founrd = true;
            }
        }
        if (!$founrd) {
            throw new ModelNotFoundException('', 404);
        }

        if (!($visit = new Visit)->save()) {
            throw new \RuntimeException('Failed to create a Visit model.', 500);
        }

        $now = new \DateTime();
        $futureVisits = RegularVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = RegularVisit::getDSRegularVisits($futureVisits, 'ASC');

        $regularOrder = RegularOrder::query()
            ->whereKey($dsRegularOrder->getId())
            ->first();

        $RegularVisit = new RegularVisit;
        $RegularVisit->{$regularOrder->getForeignKey()} = $regularOrder->{$regularOrder->getKeyName()};
        $RegularVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
        $RegularVisit->visit_timestamp = $iFindVisit->findVisit();
        $RegularVisit->consuming_time = $dsRegularOrder->getNeededTime();
        if ($iFindVisit instanceof WeeklyVisit) {
            $RegularVisit->week_days_periods = $iFindVisit->getDSWeekDaysPeriods();
        }
        // $RegularVisit->date_time_period = $dsDateTimePeriods;

        if (!$RegularVisit->save()) {
            throw new \RuntimeException('Failed to create a RegularVisit model.', 500);
        }

        return $RegularVisit->getDSRegularVisit();
    }
}
