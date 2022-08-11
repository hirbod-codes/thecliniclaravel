<?php

namespace Database\Interactions\Visits;

use App\Models\Order\LaserOrder;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\Visit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use App\UseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;

class DataBaseCreateLaserVisit implements IDataBaseCreateLaserVisit
{
    public function createLaserVisit(DSLaserOrder $dsLaserOrder, DSUser $dsTargetUser, IFindVisit $iFindVisit): DSLaserVisit
    {
        $founrd = false;
        foreach (User::query()->whereKey($dsTargetUser->getId())->first()->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null && $laserOrder->getKey() === $dsLaserOrder->getId()) {
                $founrd = true;
            }
        }
        if (!$founrd) {
            throw new ModelNotFoundException('', 404);
        }

        if (!($visit = new Visit)->save()) {
            throw new \RuntimeException('Failed to create a Visit model.', 500);
        }

        $laserOrder = LaserOrder::query()
            ->whereKey($dsLaserOrder->getId())
            ->first();

        $LaserVisit = new LaserVisit;
        $LaserVisit->{$laserOrder->getForeignKey()} = $laserOrder->{$laserOrder->getKeyName()};
        $LaserVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
        $LaserVisit->visit_timestamp = $iFindVisit->findVisit();
        $LaserVisit->consuming_time = $dsLaserOrder->getNeededTime();
        if ($iFindVisit instanceof WeeklyVisit) {
            $LaserVisit->week_days_periods = $iFindVisit->getDSWeekDaysPeriods();
        }
        // $RegularVisit->date_time_period = $dsDateTimePeriods;

        if (!$LaserVisit->save()) {
            throw new \RuntimeException('Failed to create a LaserVisit model.', 500);
        }

        return $LaserVisit->getDSLaserVisit();
    }
}
