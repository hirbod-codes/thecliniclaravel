<?php

namespace App\DataStructures\Time\Util;

use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSWeeklyTimePatterns;

class TimeDSConverter
{
    public function ConvertTimePatternToTimePeriods(DSWeeklyTimePatterns $dsWeeklyTimePatterns, DSDateTimePeriods $associatedTimePeriods): DSDateTimePeriods
    {
        $newTimePeriods = new DSDateTimePeriods;

        /** @var DSDateTimePeriod $timePeriod */
        foreach ($associatedTimePeriods as $timePeriod) {
            $newTimePeriods = $this->addWeekDay($newTimePeriods, $dsWeeklyTimePatterns, $timePeriod->getStart());

            $pointer = new \DateTime($timePeriod->getStart()->format("Y-m-d") . ' 00:00:00');
            do {
                $pointer->modify('+1 day');

                if ($pointer->getTimestamp() < $timePeriod->getEndTimestamp() && $pointer->format("Y-m-d") !== $timePeriod->getEnd()->format("Y-m-d")) {
                    $newTimePeriods = $this->addWeekDay($newTimePeriods, $dsWeeklyTimePatterns, $pointer);
                } else {
                    break;
                }
            } while (1);

            $newTimePeriods = $this->addWeekDay($newTimePeriods, $dsWeeklyTimePatterns, $timePeriod->getEnd());
        }

        return $newTimePeriods;
    }

    private function addWeekDay(DSDateTimePeriods $newTimePeriods, DSWeeklyTimePatterns $dsWeeklyTimePatterns, \DateTime $dt): DSDateTimePeriods
    {
        $date = $dt->format("Y-m-d");
        $timePatterns = $dsWeeklyTimePatterns[$dt->format("l")];

        /** @var DSTimePattern $timePattern */
        foreach ($timePatterns as $timePattern) {
            $timePeriod = new DSDateTimePeriod(
                new \DateTime($date . ' ' . $timePattern->getStart()),
                new \DateTime($date . ' ' . $timePattern->getEnd()),
            );

            try {
                $newTimePeriods[] = $timePeriod;
            } catch (TimeSequenceViolationException $th) {
            }
        }

        return $newTimePeriods;
    }
}
