<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;

trait TraitMutatorDateTimePeriod
{
    protected function dateTimePeriod(): Attribute
    {
        return Attribute::make(
            get: function (string|null $dateTiemPeriod) {
                if (is_null($dateTiemPeriod)) {
                    return null;
                }
                return DSDateTimePeriod::toObject(json_decode($dateTiemPeriod, true));
            },
            set: function (DSDateTimePeriod|array|string|null $value) {
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
