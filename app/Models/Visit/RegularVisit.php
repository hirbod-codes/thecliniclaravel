<?php

namespace App\Models\Visit;

use App\Models\Order\RegularOrder;
use App\Models\Traits\TraitDSDateTimePeriod;
use App\Models\Traits\TraitDSWeekDaysPeriods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;
use Illuminate\Support\Str;

class RegularVisit extends Model
{
    use HasFactory,
        TraitDSWeekDaysPeriods,
        TraitDSDateTimePeriod,
        TraitMutatorDateTimePeriod,
        TraitMutatorWeekDaysPeriods;

    protected $table = 'regular_visits';

    public function regularOrder(): BelongsTo
    {
        return $this->belongsTo(
            RegularOrder::class,
            (new RegularOrder)->getForeignKey(),
            (new RegularOrder)->getKeyName(),
            __FUNCTION__
        );
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(
            Visit::class,
            (new Visit)->getForeignKey(),
            (new Visit)->getKeyName(),
            __FUNCTION__
        );
    }
}
