<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeekDaysPeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;

trait TraitDSWeekDaysPeriods
{
    public function getDSWeekDaysPeriods(array|DSWeekDaysPeriods $dsWeeklyTimePatterns): DSWeekDaysPeriods
    {
        if ($dsWeeklyTimePatterns instanceof DSWeekDaysPeriods) {
            return $dsWeeklyTimePatterns;
        }

        return DSWeeklyTimePatterns::toObject($dsWeeklyTimePatterns);
    }
}
