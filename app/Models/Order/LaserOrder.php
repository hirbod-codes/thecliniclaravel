<?php

namespace App\Models\Order;

use App\Models\Package\Package;
use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaserOrder extends Model
{
    use HasFactory;

    protected $table = "laser_orders";

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
            (new Order)->getForeignKey(),
            (new Order)->getKeyName(),
            __FUNCTION__
        );
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(
            Part::class,
            (new LaserOrderPart)->getTable(),
            $this->getForeignKey(),
            (new Part)->getForeignKey(),
            $this->getKeyName(),
            (new Part)->getKeyName(),
            __FUNCTION__
        );
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(
            Package::class,
            (new LaserOrderPackage)->getTable(),
            $this->getForeignKey(),
            (new Package)->getForeignKey(),
            $this->getKeyName(),
            (new Package)->getKeyName(),
            __FUNCTION__
        );
    }
}
