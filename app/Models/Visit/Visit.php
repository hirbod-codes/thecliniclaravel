<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\DataStructures\Visit\DSVisit;
use App\DataStructures\Visit\DSVisits;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    public function laserVisit(): HasOne
    {
        return $this->hasOne(
            LaserVisit::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function regularVisit(): HasOne
    {
        return $this->hasOne(
            RegularVisit::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    /**
     * @param \Iterator|array|self[]|LaserVisit|RegularVisit|Collection $visits
     * @return DSVisits
     */
    public static function getDSVisits(\Iterator|array|Collection $visits, string $sort = 'ASC'): DSVisits
    {
        return self::getDSVisitsConditionally($visits, $sort, true);
    }

    /**
     * @param \Iterator|array|self[]|LaserVisit|RegularVisit|Collection $visits
     * @return DSVisits
     */
    public static function getMixedDSVisits(\Iterator|array|Collection $visits, string $sort = 'ASC'): DSVisits
    {
        return self::getDSVisitsConditionally($visits, $sort, false);
    }

    /**
     * @param \Iterator|array|self[]|LaserVisit[]|RegularVisit[]|Collection $visits
     * @return DSVisits
     */
    private static function getDSVisitsConditionally(\Iterator|array|Collection $visits, string $sort, bool $userSpecific): DSVisits
    {
        $dsVisits = new DSVisits('Natural');
        $first = true;
        /** @var Visit $visit */
        foreach ($visits as $visit) {
            if (!in_array(get_class($visit), [Visit::class, LaserVisit::class, RegularVisit::class])) {
                throw new \InvalidArgumentException(
                    'Only the following types are allowed: ' .
                        Visit::class . ' or ' .
                        LaserVisit::class . ' or ' .
                        RegularVisit::class . '. current given type: ' . (gettype($visit) === 'object' ? get_class($visit) : gettype($visit)),
                    500
                );
            }

            if ($visit instanceof RegularVisit) {
                $relatedOrder = 'regularOrder';
                $relatedOrderDSMethod = 'getDSRegularVisit';
                $theOrder = $visit->regularOrder;
            } elseif ($visit instanceof LaserVisit) {
                $relatedOrder = 'laserOrder';
                $relatedOrderDSMethod = 'getDSLaserVisit';
                $theOrder = $visit->laserOrder;
            } elseif ($visit instanceof Visit) {
                if ($visit->regularVisit !== null) {
                    $relatedOrder = 'regularOrder';
                    $relatedOrderDSMethod = 'getDSRegularVisit';
                    $theOrder = $visit->regularVisit->regularOrder;
                } elseif ($visit->laserVisit !== null) {
                    $relatedOrder = 'laserOrder';
                    $relatedOrderDSMethod = 'getDSLaserVisit';
                    $theOrder = $visit->laserVisit->laserOrder;
                }
            }

            if ($first && $userSpecific) {
                $first = false;
                $dsVisits = new DSVisits('Natural');
            }

            if ($visit instanceof Visit) {
                $dsVisits[] = $visit->{$relatedOrder}->{$relatedOrderDSMethod}();
            } else {
                $dsVisits[] = $visit->{$relatedOrderDSMethod}();
            }
        }

        $dsVisits->setSort($sort);

        return $dsVisits;
    }
}
