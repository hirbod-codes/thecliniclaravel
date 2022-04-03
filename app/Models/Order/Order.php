<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    public $hasPartsAndPackages = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            (new User)->getForeignKey(),
            (new User)->getKeyName(),
            __FUNCTION__
        );
    }

    public function orderable(): HasOne
    {
        /** @var \ReflectionMethod $method */
        foreach ($this->getHasOneRelationsNames() as $methodName) {
            if (($hasOne = $this->{$methodName}())->getResults() === null) {
                continue;
            }

            return $hasOne;
        }
    }

    /**
     * @return string[]
     */
    public function getHasOneRelationsNames(): array
    {
        $names = [];
        /** @var \ReflectionMethod $method */
        foreach ((new \ReflectionClass(static::class))->getMethods() as $method) {
            if ($method->getReturnType()->getName() !== HasOne::class || !Str::contains($method->getName(), 'Order')) {
                continue;
            }

            $names[] = $method->getName();
        }

        return $names;
    }

    public function laserOrder(): HasOne
    {
        return $this->hasOne(
            LaserOrder::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function regularOrder(): HasOne
    {
        return $this->hasOne(
            RegularOrder::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }
            }

            return $order;
        }
    }
}
