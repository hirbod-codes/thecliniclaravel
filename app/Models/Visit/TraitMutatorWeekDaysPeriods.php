<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\DataStructures\Time\DSWeekDaysPeriods;

trait TraitMutatorWeekDaysPeriods
{
    protected function weekDaysPeriods(): Attribute
    {
        return Attribute::make(
            get: function (string|null $weekDaysPeriods) {
                if (is_null($weekDaysPeriods)) {
                    return null;
                }
                return DSWeekDaysPeriods::toObject(json_decode($weekDaysPeriods, true));
            },
            set: function (DSWeekDaysPeriods|array|string|null $value) {
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
