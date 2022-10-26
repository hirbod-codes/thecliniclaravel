<?php

namespace App\Models\Package;

use App\Models\Model;
use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Part\Part;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\DataStructures\Order\DSPackage;
use App\DataStructures\Order\DSPackages;

/**
 * @property Collection<int, LaserOrder> $laserOrders belongsToMany
 * @property Collection<int, Part> $parts belongsToMany
 * @property string $name
 * @property string $gender
 * @property integer $price
 */
class Package extends Model
{
    use HasFactory;

    protected $table = "packages";

    protected $hidden = ['pivot'];

    public function laserOrders(): BelongsToMany
    {
        return $this->belongsToMany(
            LaserOrder::class,
            (new LaserOrderPackage)->getTable(),
            $this->getForeignKey(),
            (new LaserOrder)->getForeignKey(),
            $this->getKeyName(),
            (new LaserOrder)->getKeyName(),
            __FUNCTION__
        );
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(
            Part::class,
            (new PartPackage)->getTable(),
            $this->getForeignKey(),
            (new Part)->getForeignKey(),
            $this->getKeyName(),
            (new Part)->getKeyName(),
            __FUNCTION__
        );
    }

    public function getDSPackage(): DSPackage
    {
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args) {
            $parameterName = $parameter->getName();
            if ($parameterName === 'parts') {
                $args[$parameterName] = Part::getDSParts($this->parts->all(), $this->gender);
            } elseif ($parameterName === 'id') {
                $args[$parameterName] = $this->{$this->getKeyName()};
            } else {
                $args[$parameterName] = $this->{Str::snake($parameterName)};
            }
        }, (new \ReflectionClass(DSPackage::class))->getConstructor()->getParameters());


        return new DSPackage(...$args);
    }

    /**
     * @param self[]|Collection $packages
     * @return DSPackages
     */
    public static function getDSPackages(array|Collection $packages, string $gender): DSPackages
    {
        $dsPackages = new DSPackages($gender);
        foreach ($packages as $package) {
            if (!($package instanceof Package)) {
                throw new \InvalidArgumentException('The variable $package must be of type: ' . Package::class, 500);
            }
            $dsPackages[] = $package->getDSPackage();
        }

        return $dsPackages;
    }
}
