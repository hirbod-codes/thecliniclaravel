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

    public function getDSRegularVisit(): DSRegularVisit
    {
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args) {
            $parameterName = $parameter->getName();

            $this->collectDSArgs($args, $parameterName);
        }, (new \ReflectionClass(DSRegularVisit::class))->getConstructor()->getParameters());

        return new DSRegularVisit(...$args);
    }

    private function collectDSArgs(array &$args, string $parameterName)
    {
        if ($parameterName === 'id') {
            $args[$parameterName] = $this->{$this->getKeyName()};
        } elseif ($parameterName === 'weekDaysPeriods') {
            if (($weekDaysPeriods = $this->week_days_periods) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = $this->getDSWeekDaysPeriods($weekDaysPeriods);
            }
        } elseif ($parameterName === 'dateTimePeriod') {
            if (($dateTimePeriod = $this->date_time_period) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = $this->getDSDateTimePeriod($dateTimePeriod);
            }
        } else {
            $args[$parameterName] = $this->{Str::snake($parameterName)};
        }
    }

    /**
     * @param \Iterator|self[]|Collection $regularVisits
     * @return DSRegularVisits
     */
    public static function getDSRegularVisits(array|Collection $regularVisits, string $sort): DSRegularVisits
    {

        return self::getDSRegularVisitsConditionally($regularVisits, $sort, true);
    }

    /**
     * @param \Iterator|self[]|Collection $regularVisits
     * @return DSRegularVisits
     */
    public static function getMixedDSRegularVisits(array|Collection $regularVisits, string $sort): DSRegularVisits
    {
        return self::getDSRegularVisitsConditionally($regularVisits, $sort, false);
    }

    /**
     * @param \Iterator|self[]|Collection $regularVisits
     * @return DSRegularVisits
     */
    public static function getDSRegularVisitsConditionally(array|Collection $regularVisits, string $sort, bool $userSpecific): DSRegularVisits
    {
        $dsRegularVisits = new DSRegularVisits('Natural');

        if (count($regularVisits) === 0) {
            return $dsRegularVisits;
        }

        $first = true;
        /** @var self $regularVisit */
        foreach ($regularVisits as $regularVisit) {
            if (!($regularVisit instanceof RegularVisit)) {
                throw new \InvalidArgumentException('The variable $regularVisit must be of type: ' . RegularVisit::class, 500);
            }

            if ($first && $userSpecific) {
                $first = false;

                $dsRegularVisits = new DSRegularVisits('Natural');
            }

            $dsRegularVisits[] = $regularVisit->getDSRegularVisit();
        }

        $dsRegularVisits->setSort($sort);

        return $dsRegularVisits;
    }
}
