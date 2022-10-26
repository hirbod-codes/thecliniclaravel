<?php

namespace App\Models\Order;

use App\Models\User;
use App\Models\Model;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\DataStructures\Order\DSOrders;

/**
 * @property User $user belongsTo
 * @property orderable $orderable belongsTo
 * @property LaserOrder $laserOrder hasOne
 * @property RegularOrder $regularOrder hasOne
 * @property int $user_id FK -> User
 */
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

    /**
     * @param \Iterator|array|self[]|LaserOrder[]|RegularOrder[]|Collection $orders
     * @return DSOrders
     */
    public static function getDSOrders(\Iterator|array|Collection $orders): DSOrders
    {
        return self::getDSOrdersConditionally($orders, true);
    }

    /**
     * @param \Iterator|array|self[]|LaserOrder[]|RegularOrder[]|Collection $orders
     * @return DSOrders
     */
    public static function getMixedDSOrders(\Iterator|array|Collection $orders): DSOrders
    {
        return self::getDSOrdersConditionally($orders, false);
    }

    /**
     * @param \Iterator|array|self[]|LaserOrder[]|RegularOrder[]|Collection $orders
     * @param boolean $userSpecific
     * @return DSOrders
     */
    public static function getDSOrdersConditionally(\Iterator|array|Collection $orders, bool $userSpecific): DSOrders
    {
        $dsOrders = new DSOrders();
        $first = true;
        foreach ($orders as $order) {
            if (!in_array(get_class($order), [Order::class, LaserOrder::class, RegularOrder::class])) {
                throw new \InvalidArgumentException(
                    'Only the following types are allowed: ' .
                        Order::class . ' or ' .
                        LaserOrder::class . ' or ' .
                        RegularOrder::class . '.',
                    500
                );
            }

            if ($first && $userSpecific) {
                $first = false;

                if ($order instanceof Order) {
                    $dsOrders = new DSOrders($order->user);
                } elseif ($order instanceof RegularOrder) {
                    $dsOrders = new DSOrders($order->order->user);
                } elseif ($order instanceof LaserOrder) {
                    $dsOrders = new DSOrders($order->order->user);
                }
            }

            if ($order instanceof Order) {
                if ($order->laserOrder !== null) {
                    $dsOrders[] = $order->laserOrder->getDSLaserOrder();
                } elseif ($order->regularOrder !== null) {
                    $dsOrders[] = $order->regularOrder->getDSRegularOrder();
                }
            } elseif ($order instanceof RegularOrder) {
                $dsOrders[] = $order->getDSRegularOrder();
            } elseif ($order instanceof LaserOrder) {
                $dsOrders[] = $order->getDSLaserOrder();
            }
        }

        return $dsOrders;
    }
}
