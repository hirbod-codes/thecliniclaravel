<?php

namespace Database\Interactions\Visits\Creation;

use App\Models\Order\LaserOrder;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\Visit;
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
                $laserVisit->week_days_periods = $iFindVisit->getDSWeekDaysPeriods();
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
