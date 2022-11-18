<?php

namespace Database\Interactions\Visits\Creation;

use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;
use App\Models\Visit\Visit;
use App\PoliciesLogic\Visit\CustomVisit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateRegularVisit;
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
                $regularVisit->weekly_time_patterns = $iFindVisit->getDSTimePatterns();
            }

            if ($iFindVisit instanceof CustomVisit) {
                $regularVisit->date_time_periods = $iFindVisit->getDSDateTimePeriods();
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
