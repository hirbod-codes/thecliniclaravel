<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\DataStructures\Time\DSDateTimePeriods;

trait TraitMutatorDateTimePeriods
{
    protected function dateTimePeriods(): Attribute
    {
        return Attribute::make(
            get: function (string|null $dateTiemPeriods) {
                if (is_null($dateTiemPeriods)) {
                    return null;
                }
                return DSDateTimePeriods::toObject(json_decode($dateTiemPeriods, true));
            },
            set: function (DSDateTimePeriods|array|string|null $value) {
                if ($value instanceof DSDateTimePeriods) {
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
