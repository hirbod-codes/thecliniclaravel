<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSDateTimePeriods;

trait TraitDSDateTimePeriod
{
    public function getDSDateTimePeriod(array|DSDateTimePeriods $dateTimePeriods): DSDateTimePeriods
    {
        if ($dateTimePeriods instanceof DSDateTimePeriods) {
            return $dateTimePeriods;
        }

        return DSDateTimePeriods::toObject($dateTimePeriods);
    }
}
