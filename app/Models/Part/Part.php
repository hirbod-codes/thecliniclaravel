<?php

namespace App\Models\Part;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPart;
use App\Models\Package\Package;
use App\Models\Package\PartPackage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use TheClinicDataStructures\DataStructures\Order\DSPart;
use TheClinicDataStructures\DataStructures\Order\DSParts;

class Part extends Model
{
    use HasFactory;

    protected $table = "parts";

    public function laserOrders(): BelongsToMany
    {
        return $this->belongsToMany(
            LaserOrder::class,
            (new LaserOrderPart)->getTable(),
            $this->getForeignKey(),
            (new LaserOrder)->getForeignKey(),
            $this->getKeyName(),
            (new LaserOrder)->getKeyName(),
            __FUNCTION__
        );
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(
            Package::class,
            (new PartPackage)->getTable(),
            $this->getForeignKey(),
            (new Package)->getForeignKey(),
            $this->getKeyName(),
            (new Package)->getKeyName(),
            __FUNCTION__
        );
    }

    public function getDSPart(): DSPart
    {
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args) {
            if (($parameterName = $parameter->getName()) === 'id') {
                $args[$parameterName] = $this->getAttributeFromArray($this->getKeyName());
            } elseif ($parameter->getType()->getName() === 'DateTime') {
                $args[$parameterName] = new \DateTime($this->getAttributeFromArray(Str::snake($parameterName)));
            } else {
                $args[$parameterName] = $this->getAttributeFromArray(Str::snake($parameterName));
            }
        }, (new \ReflectionClass(DSPart::class))->getConstructor()->getParameters());


        return new DSPart(...$args);
    }

    /**
     * @param \App\Models\Part\Part[] $parts
     * @return DSParts
     */
    public static function getDSParts(array $parts, string $gender): DSParts
    {
        $dsParts = new DSParts($gender);
        foreach ($parts as $part) {
            $dsParts[] = $part->getDSPart();
        }

        return $dsParts;
    }
}
