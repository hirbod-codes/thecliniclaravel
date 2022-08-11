<?php

namespace App\Models\Traits;

use App\DataStructures\Time\DSDateTimePeriod;

trait TraitDSDateTimePeriod
{
    public function getDSDateTimePeriod(array|DSDateTimePeriod $dateTimePeriod): DSDateTimePeriod
    {
        if ($dateTimePeriod instanceof DSDateTimePeriod) {
            return $dateTimePeriod;
        }

        return new DSDateTimePeriod(
            new \DateTime($dateTimePeriod['start']),
            new \DateTime($dateTimePeriod['end'])
        );
    }
}
