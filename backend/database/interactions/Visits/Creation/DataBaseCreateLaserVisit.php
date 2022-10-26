<?php

namespace Database\Interactions\Visits\Creation;

use App\Models\Order\LaserOrder;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\Visit;
use App\PoliciesLogic\Visit\CustomVisit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateLaserVisit;
use Illuminate\Support\Facades\DB;

class DataBaseCreateLaserVisit implements IDataBaseCreateLaserVisit
{
    public function createLaserVisit(LaserOrder $laserOrder, IFindVisit $iFindVisit): LaserVisit
    {
        try {
            DB::beginTransaction();

            $visit = new Visit;
            $visit->saveOrFail();

            $laserVisit = new LaserVisit;

            $laserVisit->{$laserOrder->getForeignKey()} = $laserOrder->getKey();
            $laserVisit->{$visit->getForeignKey()} = $visit->getKey();
            $laserVisit->visit_timestamp = $iFindVisit->findVisit();
            $laserVisit->consuming_time = $laserOrder->needed_time;

            if ($iFindVisit instanceof WeeklyVisit) {
                $laserVisit->weekly_time_patterns = $iFindVisit->getDSTimePatterns();
            }

            if ($iFindVisit instanceof CustomVisit) {
                $laserVisit->date_time_periods = $iFindVisit->getDSDateTimePeriods();
            }

            $laserVisit->saveOrFail();

            DB::commit();

            return $laserVisit;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
