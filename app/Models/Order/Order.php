<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            (new User)->getForeignKey(),
            (new User)->getKeyName(),
            __FUNCTION__
        );
    }

    public function orderable(): Model
    {
        foreach (scandir(__DIR__) as $filename) {
            $filename = str_replace('.php', '', array_pop(explode('/', str_replace('\\', '', $filename))));

            if ($filename === 'Order') {
                continue;
            }

            if (($order = $this->hasOne('App\\Models\\Order\\' . $filename, $this->getForeignKey(), $this->getKeyName(), __FUNCTION__)->first()) === null) {
                continue;
            }

            return $order;
        }
    }
}
