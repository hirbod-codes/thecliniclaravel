<?php

namespace App\Models\Visit;

use App\Models\Order\LaserOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;

class LaserVisit extends Model
{
    use HasFactory;

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
}
