<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Exceptions\Visit\InvalidConsumingTime;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;

class ValidateTimeRanges
{
    /**
     * @param DSWeeklyTimePatterns $workSchedule
     * @param integer $consumingTime
     * @return void
     *
     * @throws NeededTimeOutOfRange
     */
    public function checkConsumingTimeInWorkSchedule(DSWeeklyTimePatterns $workSchedule, int $consumingTime): void
    {
        $found = false;

        /**
         * @var string $weekDay
         * @var DSTimePatterns $dsTimePatterns
         */
        foreach ($workSchedule as $weekDay => $dsTimePatterns) {
            /** @var DSTimePattern $dsTimePattern */
            foreach ($dsTimePatterns as $dsTimePattern) {
                if (((new \DateTime($dsTimePattern->getEnd()))->getTimestamp() - (new \DateTime($dsTimePattern->getStart()))->getTimestamp()) >= $consumingTime) {
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
     * @throws InvalidConsumingTime
     */
    public function checkConsumingTimeInTimeRange(int $firstTS, int $lastTS, int $consumingTime): void
    {
        if (
            $lastTS <= $firstTS ||
            ($lastTS - $firstTS) < $consumingTime
        ) {
            throw new InvalidConsumingTime();
        }
    }
}
