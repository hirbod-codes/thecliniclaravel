<?php

namespace App\Models\Order;

use App\Models\Package\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaserOrderPackage extends Model
{
    use HasFactory;

    protected $table = "laser_orders_packages";

    public function part(): BelongsTo
    {
        return $this->belongsTo(
            Package::class,
            (new Package)->getForeignKey(),
            (new Package)->getKeyName(),
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
