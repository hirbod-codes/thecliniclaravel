<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegularOrder extends Model
{
    use HasFactory;

    protected $table = "regular_orders";

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
            (new Order)->getForeignKey(),
            (new Order)->getKeyName(),
            __FUNCTION__
        );
    }
}
