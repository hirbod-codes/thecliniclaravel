<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeekDaysPeriods;

trait TraitDSWeekDaysPeriods
{
    public function getDSWeekDaysPeriods(array|DSWeekDaysPeriods $weekDaysPeriods): DSWeekDaysPeriods
    {
        if ($weekDaysPeriods instanceof DSWeekDaysPeriods) {
            return $weekDaysPeriods;
        }

        $dsWeekDaysPeriods = new DSWeekDaysPeriods('Monday');

        foreach ($weekDaysPeriods as $weekDay => $periods) {
            $dsPeriods = new DSDateTimePeriods;
            foreach ($periods as $period) {
                $dsPeriods[] = new DSDateTimePeriod($period['start'], $period['end']);
            }

            $dsWeekDaysPeriods[$weekDay] = $dsPeriods;
        }

        return $dsWeekDaysPeriods;
    }
}
