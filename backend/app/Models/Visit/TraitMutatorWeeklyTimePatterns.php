<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\DataStructures\Time\DSWeeklyTimePatterns;

trait TraitMutatorWeeklyTimePatterns
{
    protected function weeklyTimePatterns(): Attribute
    {
        return Attribute::make(
            get: function (string|null $weeklyTimePatterns) {
                if (is_null($weeklyTimePatterns)) {
                    return null;
                }
                return DSWeeklyTimePatterns::toObject(json_decode($weeklyTimePatterns, true));
            },
            set: function (DSWeeklyTimePatterns|array|string|null $value) {
                if ($value instanceof DSWeeklyTimePatterns) {
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
