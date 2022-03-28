<?php

namespace App\Models\Package;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSPackages;

class Package extends Model
{
    use HasFactory;

    protected $table = "packages";

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
                $args[$parameterName] = Part::getDSParts($this->parts()->get()->all(), $this->getAttributeFromArray('gender'));
            } elseif ($parameterName === 'id') {
                $args[$parameterName] = $this->getAttributeFromArray($this->getKeyName());
            } elseif ($parameter->getType()->getName() === 'DateTime') {
                $args[$parameterName] = new \DateTime($this->getAttributeFromArray(Str::snake($parameterName)));
            } else {
                $args[$parameterName] = $this->getAttributeFromArray(Str::snake($parameterName));
            }
        }, (new \ReflectionClass(DSPackage::class))->getConstructor()->getParameters());


        return new DSPackage(...$args);
    }

    /**
     * @param \App\Models\Package\Package[] $packages
     * @return DSPackages
     */
    public static function getDSPackages(array $packages, string $gender): DSPackages
    {
        $dsPackages = new DSPackages($gender);
        foreach ($packages as $package) {
            $dsPackages[] = $package->getDSPackage();
        }

        return $dsPackages;
    }
}
