<?php

namespace App\Models\Order;

use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Order\LaserOrder;

/**
 * @property Part $part belongsTo
 * @property LaserOrder $laserOrder belongsTo
 */
class LaserOrderPart extends Model
{
    use HasFactory;

    protected $table = "laser_orders_parts";

    public function part(): BelongsTo
    {
        return $this->belongsTo(
            Part::class,
            (new Part)->getForeignKey(),
            (new Part)->getKeyName(),
            __FUNCTION__
        );
    }

    public function laserOrder(): BelongsTo
    {
        return $this->belongsTo(
            LaserOrder::class,
            (new LaserOrder)->getForeignKey(),
            (new LaserOrder)->getKeyName(),
            __FUNCTION__
        );
    }
}
