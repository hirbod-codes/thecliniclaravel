<?php

namespace App\Models\Visit;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Models\Order\RegularOrder;
use App\Models\Traits\TraitDSDateTimePeriods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\DataStructures\Visit\Regular\DSRegularVisit;
use App\DataStructures\Visit\Regular\DSRegularVisits;
use App\Models\Traits\TraitDSWeeklyTimePatterns;
use Illuminate\Support\Str;

/**
 * @property RegularOrder $regularOrder belongsTo
 * @property Visit $visit belongsTo
 * @property int $regular_visits_regular_orders_regular_order_id FK -> regularOrder
 * @property int $regular_visits_visits_visit_id FK -> Visit
 * @property int $regular_visits_visits_guards_visits_guard_id FK -> visits_guard
 * @property int $visit_timestamp
 * @property integer $consuming_time
 * @property DSWeeklyTimePatterns $weekly_time_patterns json
 * @property DSDateTimePeriods $date_time_periods json
 * @property boolean $visitor_reminded
 */
class RegularVisit extends Model
{
    use HasFactory,
        TraitDSWeeklyTimePatterns,
        TraitDSDateTimePeriods,
        TraitMutatorDateTimePeriods,
        TraitMutatorWeeklyTimePatterns;

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

    private function collectDSArgs(array &$args, string $parameterName): void
    {
        if ($parameterName === 'id') {
            $args[$parameterName] = $this->{$this->getKeyName()};
        } elseif ($parameterName === 'weeklyTimePatterns') {
            if (($weeklyTimePatterns = $this->weekly_time_patterns) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = $this->getDSWeeklyTimePatterns($weeklyTimePatterns);
            }
        } elseif ($parameterName === 'dateTimePeriods') {
            if (($dateTimePeriod = $this->date_time_period) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = $this->getDSDateTimePeriods($dateTimePeriod);
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
