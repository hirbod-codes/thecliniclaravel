<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSWeeklyTimePatterns;

trait TraitDSWeeklyTimePatterns
{
    public function getDSWeeklyTimePatterns(array|DSWeeklyTimePatterns $dsWeeklyTimePatterns): DSWeeklyTimePatterns
    {
        if ($dsWeeklyTimePatterns instanceof DSWeeklyTimePatterns) {
            return $dsWeeklyTimePatterns;
        }

        return DSWeeklyTimePatterns::toObject($dsWeeklyTimePatterns);
    }
}
