<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;

class BusinessDefault extends Model
{
    use HasFactory;

    protected $table = "business_defaults";

    protected function workSchedule(): Attribute
    {
        return Attribute::make(
            set: function (DSWorkSchedule $value) {
                return json_encode($value->toArray());
            }
        );
    }

    protected function downTimes(): Attribute
    {
        return Attribute::make(
            set: function (DSDownTimes $value) {
                return json_encode($value->toArray());
            }
        );
    }
}
