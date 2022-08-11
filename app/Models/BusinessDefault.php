<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWorkSchedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessDefault extends Model
{
    use HasFactory;

    protected $table = "business_defaults";

    public function business(): BelongsTo
    {
        return $this->belongsTo(
            Business::class,
            (new Business)->getForeignKey(),
            (new Business)->getKeyName(),
            __FUNCTION__
        );
    }

    protected function workSchedule(): Attribute
    {
        return Attribute::make(
            get: function (string $workSchedule): DSWorkSchedule {
                return DSWorkSchedule::toObject(json_decode($workSchedule, true));
            },
            set: function (DSWorkSchedule|array|string $value) {
                if (is_array($value)) {
                    return json_encode($value);
                } elseif (is_string($value)) {
                    return $value;
                } else {
                    return json_encode($value->toArray());
                }
            }
        );
    }

    protected function downTimes(): Attribute
    {
        return Attribute::make(
            get: function (string $downTimes): DSDownTimes {
                return DSDownTimes::toObject(json_decode($downTimes, true));
            },
            set: function (DSDownTimes|array|string $value) {
                if (is_array($value)) {
                    return json_encode($value);
                } elseif (is_string($value)) {
                    return $value;
                } else {
                    return json_encode($value->toArray());
                }
            }
        );
    }

    protected function genders(): Attribute
    {
        return Attribute::make(
            get: function (string $genders): array {
                return json_decode($genders, true);
            },
            set: function (array $genders) {
                return json_encode($genders);
            }
        );
    }
}
