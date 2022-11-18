<?php

namespace App\Models\Order;

use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\Order\Order;
use App\Models\Model;
use App\Models\Visit\LaserVisit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\DataStructures\Order\Laser\DSLaserOrder;
use App\DataStructures\Order\Laser\DSLaserOrders;

/**
 * @property Order $order belongsTo
 * @property Collection<int, Part> $parts belongsToMany
 * @property Collection<int, Package> $packages belongsToMany
 * @property Collection<int, LaserVisit> $laserVisits belongsToMany
 * @property int $laser_orders_orders_order_id FK -> Order
 * @property int $orders_orders_guard_order_id FK -> Order
 * @property integer $price
 * @property integer $price_with_discount
 * @property integer $needed_time
 */
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

    public function laserVisits(): HasMany
    {
        return $this->hasMany(
            LaserVisit::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function getDSLaserOrder(): DSLaserOrder
    {
        $args = [];
        $gender = $this->order->user->gender;

        array_map(function (\ReflectionParameter $parameter) use (&$args, $gender) {
            $parameterName = $parameter->getName();

            $this->collectDSArgs($args, $parameterName, $gender);
        }, (new \ReflectionClass(DSLaserOrder::class))->getConstructor()->getParameters());

        return new DSLaserOrder(...$args);
    }

    private function collectDSArgs(array &$args, string $parameterName, string $gender)
    {
        if ($parameterName === 'id') {
            $args[$parameterName] = $this->{$this->getKeyName()};
        } elseif ($parameterName === 'userId') {
            $args[$parameterName] = $this->order->user->{$this->getKeyName()};
        } elseif ($parameterName === 'gender') {
            $args[$parameterName] = $gender;
        } elseif ($parameterName === 'parts') {
            $args[$parameterName] = Part::getDSParts($this->parts, $gender);
        } elseif ($parameterName === 'packages') {
            $args[$parameterName] = Package::getDSPackages($this->packages, $gender);
        } elseif ($parameterName === 'visits') {
            if (($visits = $this->laserVisits) === null) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = LaserVisit::getDSLaserVisits($visits, 'ASC');
            }
        } else {
            $args[$parameterName] = $this->{Str::snake($parameterName)};
        }
    }

    /**
     * @param self[]|Order[]|Collection $orders
     * @return DSLaserOrders
     */
    public static function getDSLaserOrders(array|Collection $orders): DSLaserOrders
    {
        return self::getDSLaserOrdersConditionally($orders, true);
    }

    /**
     * @param self[]|Order[]|Collection $orders
     * @return DSLaserOrders
     */
    public static function getMixedDSLaserOrders(array|Collection $orders): DSLaserOrders
    {
        return self::getDSLaserOrdersConditionally($orders, false);
    }

    /**
     * @param bool $userSpecific
     * @param self[]|Order[]|RegularOrder[]|Collection $orders
     * @return DSLaserOrders
     */
    private static function getDSLaserOrdersConditionally(array|Collection $orders, bool $userSpecific): DSLaserOrders
    {
        $dsLaserOrders = new DSLaserOrders();
        $first = true;
        foreach ($orders as $order) {
            if (!($order instanceof Order || $order instanceof LaserOrder)) {
                throw new \InvalidArgumentException('The variable $order must be of type: ' . Order::class . ' or ' . LaserOrder::class, 500);
            }
            if ($first && $userSpecific) {
                $first = false;
                if ($order instanceof Order) {
                    $dsLaserOrders = new DSLaserOrders($order->user);
                } elseif ($order instanceof LaserOrder) {
                    $dsLaserOrders = new DSLaserOrders($order->order->user);
                } else {
                    $first = true;
                    continue;
                }
            }

            if ($order instanceof Order) {
                $laserOrder = $order->laserOrder;
                if ($laserOrder === null) {
                    continue;
                }
            } elseif ($order instanceof LaserOrder) {
                $laserOrder = $order;
            } else {
                throw new \InvalidArgumentException('Order instances must be of types: ' . Order::class . ' or ' . static::class, 500);
            }

            $dsLaserOrders[] = $laserOrder->getDSLaserOrder();
        }

        return $dsLaserOrders;
    }
}
