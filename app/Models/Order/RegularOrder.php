<?php

namespace App\Models\Order;

use App\Models\Order\Order;
use App\Models\Model;
use App\Models\Visit\RegularVisit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\DataStructures\Order\Regular\DSRegularOrder;
use App\DataStructures\Order\Regular\DSRegularOrders;

/**
 * @property Order $order belongsTo
 * @property Collection<int, RegularVisits> $regularVisits belongsToMany
 * @property int $laser_orders_orders_order_id FK -> Order
 * @property int $orders_orders_guard_order_id FK -> Order
 * @property integer $price
 * @property integer $needed_time
 */
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

    public function regularVisits(): HasMany
    {
        return $this->hasMany(
            RegularVisit::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function getDSRegularOrder(): DSRegularOrder
    {
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args) {
            $parameterName = $parameter->getName();

            $this->collectDSArgs($args, $parameterName);
        }, (new \ReflectionClass(DSRegularOrder::class))->getConstructor()->getParameters());

        return new DSRegularOrder(...$args);
    }

    private function collectDSArgs(array &$args, string $parameterName)
    {
        if ($parameterName === 'id') {
            $args[$parameterName] = $this->{$this->getKeyName()};
        } elseif ($parameterName === 'userId') {
            $args[$parameterName] = $this->order->user->{$this->getKeyName()};
        } elseif ($parameterName === 'visits') {
            if (($visits = $this->regularVisits) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = RegularVisit::getDSRegularVisits($visits, 'ASC');
            }
        } else {
            $args[$parameterName] = $this->{Str::snake($parameterName)};
        }
    }

    /**
     * @param self[]|Order[]|Collection $orders
     * @return DSRegularOrders
     */
    public static function getDSRegularOrders(array|Collection $orders): DSRegularOrders
    {
        return self::getDSRegularOrdersConditionally($orders, true);
    }

    /**
     * @param self[]|Order[]|Collection $orders
     * @return DSRegularOrders
     */
    public static function getMixedDSRegularOrders(array|Collection $orders): DSRegularOrders
    {
        return self::getDSRegularOrdersConditionally($orders, false);
    }

    /**
     * @param bool $userSpecific
     * @param self[]|LaserOrder[]|Order[]|Collection $orders
     * @return DSRegularOrders
     */
    private static function getDSRegularOrdersConditionally(array|Collection $orders, bool $userSpecific): DSRegularOrders
    {
        $dsRegularOrders = new DSRegularOrders();
        $first = true;
        foreach ($orders as $order) {
            if (!($order instanceof Order || $order instanceof RegularOrder)) {
                throw new \InvalidArgumentException('The variable $order must be of type: ' . Order::class . ' or ' . RegularOrder::class, 500);
            }
            if ($first && $userSpecific) {
                $first = false;
                if ($order instanceof Order) {
                    $dsRegularOrders = new DSRegularOrders($order->user);
                } elseif ($order instanceof RegularOrder) {
                    $dsRegularOrders = new DSRegularOrders($order->order->user);
                } else {
                    $first = true;
                    continue;
                }
            }

            if ($order instanceof Order) {
                $regularOrder = $order->regularOrder;
                if ($regularOrder === null) {
                    continue;
                }
            } elseif ($order instanceof RegularOrder) {
                $regularOrder = $order;
            } else {
                throw new \InvalidArgumentException('Order instances must be of types: ' . Order::class . ' or ' . static::class, 500);
            }

            $dsRegularOrders[] = $regularOrder->getDSRegularOrder();
        }

        return $dsRegularOrders;
    }
}
