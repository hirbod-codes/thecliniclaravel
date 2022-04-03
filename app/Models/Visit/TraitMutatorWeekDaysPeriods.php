<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

trait TraitMutatorWeekDaysPeriods
{
    protected function weekDaysPeriods(): Attribute
    {
        return Attribute::make(
            set: function (DSWeekDaysPeriods|array|string $value) {
                if ($value instanceof DSWeekDaysPeriods) {
                    return json_encode($value->toArray());
                } elseif (gettype($value) === 'array') {
                    return json_encode($value);
                } else {
                    return $value;
                }
            }
        );
    }
}
