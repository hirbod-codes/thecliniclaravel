<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSDateTimePeriods;

trait TraitDSDateTimePeriods
{
    public function getDSDateTimePeriods(array|DSDateTimePeriods $dateTimePeriods): DSDateTimePeriods
    {
        if ($dateTimePeriods instanceof DSDateTimePeriods) {
            return $dateTimePeriods;
        }

        return DSDateTimePeriods::toObject($dateTimePeriods);
    }
}
