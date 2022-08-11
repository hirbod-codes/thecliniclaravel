<?php

namespace Database\Interactions\Visits;

use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;
use App\Models\Visit\Visit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use App\UseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;
use Illuminate\Support\Facades\DB;

class DataBaseCreateRegularVisit implements IDataBaseCreateRegularVisit
{
    public function createRegularVisit(RegularOrder $regularOrder, IFindVisit $iFindVisit): RegularVisit
    {
        try {
            DB::beginTransaction();

            $visit = new Visit;
            $visit->saveOrFail();

            $regularVisit = new RegularVisit;
            $regularVisit->{$regularOrder->getForeignKey()} = $regularOrder->getKey();
            $regularVisit->{$visit->getForeignKey()} = $visit->getKey();
            $regularVisit->visit_timestamp = $iFindVisit->findVisit();
            $regularVisit->consuming_time = $regularOrder->needed_time;

            if ($iFindVisit instanceof WeeklyVisit) {
                $regularVisit->week_days_periods = $iFindVisit->getDSWeekDaysPeriods();
            }

            $regularVisit->saveOrFail();

            DB::commit();

            return $regularVisit;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
