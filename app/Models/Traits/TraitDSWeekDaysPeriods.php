<?php

namespace App\Models\Traits;

use TheClinicDataStructures\DataStructures\Time\DSTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

trait TraitDSWeekDaysPeriods
{
    public function getDSWeekDaysPeriods(array $weekDaysPeriods): DSWeekDaysPeriods
    {
        $dsWeekDaysPeriods = new DSWeekDaysPeriods('Monday');

        foreach ($weekDaysPeriods as $weekDay => $periods) {
            $dsPeriods = new DSTimePeriods;
            foreach ($periods as $period) {
                $dsPeriods[] = new DSTimePeriod($period['start'], $period['end']);
            }

            $dsWeekDaysPeriods[$weekDay] = $dsPeriods;
        }

        return $dsWeekDaysPeriods;
    }
}
