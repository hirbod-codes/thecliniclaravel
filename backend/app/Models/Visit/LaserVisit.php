<?php

namespace App\Models\Visit;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Models\Order\LaserOrder;
use App\Models\Traits\TraitDSDateTimePeriods;
use App\Models\Traits\TraitDSWeeklyTimePatterns;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\DataStructures\Visit\Laser\DSLaserVisit;
use App\DataStructures\Visit\Laser\DSLaserVisits;

/**
 * @property LaserOrder $laserOrder belongsTo
 * @property Visit $visit belongsTo
 * @property int $laser_visits_laser_orders_laser_order_id FK -> LaserOrder
 * @property int $laser_visits_visits_visit_id FK -> Visit
 * @property int $laser_visits_visits_guards_visits_guard_id FK -> visits_guard
 * @property int $visit_timestamp
 * @property integer $consuming_time
 * @property DSWeeklyTimePatterns $weekly_time_patterns json
 * @property DSDateTimePeriods $date_time_periods json
 * @property boolean $visitor_reminded
 */
class LaserVisit extends Model
{
    use HasFactory,
        TraitDSWeeklyTimePatterns,
        TraitDSDateTimePeriods,
        TraitMutatorDateTimePeriods,
        TraitMutatorWeeklyTimePatterns;

    protected $table = 'laser_visits';

    public function laserOrder(): BelongsTo
    {
        return $this->belongsTo(
            LaserOrder::class,
            (new LaserOrder)->getForeignKey(),
            (new LaserOrder)->getKeyName(),
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

    public function getDSLaserVisit(): DSLaserVisit
    {
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args) {
            $parameterName = $parameter->getName();

            $this->collectDSArgs($args, $parameterName);
        }, (new \ReflectionClass(DSLaserVisit::class))->getConstructor()->getParameters());

        return new DSLaserVisit(...$args);
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
     * @param \Iterator|self[]|Collection $laserVisits
     * @return DSLaserVisits
     */
    public static function getDSLaserVisits(array|Collection $laserVisits, string $sort): DSLaserVisits
    {
        return self::getDSLaserVisitsConditionally($laserVisits, $sort, true);
    }

    /**
     * @param \Iterator|self[]|Collection $laserVisits
     * @return DSLaserVisits
     */
    public static function getMixedDSLaserVisits(array|Collection $laserVisits, string $sort): DSLaserVisits
    {
        return self::getDSLaserVisitsConditionally($laserVisits, $sort, false);
    }

    /**
     * @param \Iterator|self[]|Collection $laserVisits
     * @return DSLaserVisits
     */
    public static function getDSLaserVisitsConditionally(array|Collection $laserVisits, string $sort, bool $userSpecific): DSLaserVisits
    {
        $dsLaserVisits = new DSLaserVisits('Natural');

        if (count($laserVisits) === 0) {
            return $dsLaserVisits;
        }

        $first = true;
        /** @var self $laserVisit */
        foreach ($laserVisits as $laserVisit) {
            if (!($laserVisit instanceof LaserVisit)) {
                throw new \InvalidArgumentException('The variable $laserVisit must be of type: ' . laserVisit::class, 500);
            }

            if ($first && $userSpecific) {
                $first = false;

                $dsLaserVisits = new DSLaserVisits('Natural');
            }

            $dsLaserVisits[] = $laserVisit->getDSLaserVisit();
        }

        $dsLaserVisits->setSort($sort);

        return $dsLaserVisits;
    }
}
