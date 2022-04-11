<?php

namespace App\Models\Traits;

use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

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
