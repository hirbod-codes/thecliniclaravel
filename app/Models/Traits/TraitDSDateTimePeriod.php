<?php

namespace App\Models\Traits;

use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;

trait TraitDSDateTimePeriod
{
    public function getDSDateTimePeriod(array $dateTimePeriod): DSDateTimePeriod
    {
        return new DSDateTimePeriod(
            new \DateTime($dateTimePeriod['start']),
            new \DateTime($dateTimePeriod['end'])
        );
    }
}
