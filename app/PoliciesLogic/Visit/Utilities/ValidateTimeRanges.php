<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWorkSchedule;

class ValidateTimeRanges
{
    /**
     * @param DSWorkSchedule $dsWorkSchedule
     * @param integer $consumingTime
     * @return void
     *
     * @throws NeededTimeOutOfRange
     */
    public function checkConsumingTimeInWorkSchedule(DSWorkSchedule $dsWorkSchedule, int $consumingTime): void
    {
        $found = false;

        /**
         * @var string $weekDay
         * @var DSDateTimePeriods $dsDateTimePeriods
         */
        foreach ($dsWorkSchedule as $weekDay => $dsDateTimePeriods) {
            /** @var DSDateTimePeriod $dsDateTimePeriod */
            foreach ($dsDateTimePeriods as $dsDateTimePeriod) {
                if (($dsDateTimePeriod->getEndTimestamp() - $dsDateTimePeriod->getStartTimestamp()) >= $consumingTime) {
                    $found = true;
                }
            }
        }

        if (!$found) {
            throw new NeededTimeOutOfRange('There is not enough time for this order in the given work schedule.', 500);
        }
    }

    /**
     * @param integer $firstTS
     * @param integer $lastTS
     * @param integer $consumingTime
     * @return void
     *
     * @throws NeededTimeOutOfRange
     */
    public function checkConsumingTimeInTimeRange(int $firstTS, int $lastTS, int $consumingTime): void
    {
        if (
            $lastTS <= $firstTS ||
            ($lastTS - $firstTS) < $consumingTime
        ) {
            throw new NeededTimeOutOfRange();
        }
    }
}
