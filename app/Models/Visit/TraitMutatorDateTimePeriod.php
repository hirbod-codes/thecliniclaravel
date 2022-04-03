<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;

trait TraitMutatorDateTimePeriod
{
    protected function dateTimePeriod(): Attribute
    {
        return Attribute::make(
            set: function (DSDateTimePeriod|array|string $value) {
                if ($value instanceof DSDateTimePeriod) {
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
