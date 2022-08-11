<?php

namespace App\Models\Part;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPart;
use App\Models\Package\Package;
use App\Models\Package\PartPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\DataStructures\Order\DSPart;
use App\DataStructures\Order\DSParts;

class Part extends Model
{
    use HasFactory;

    protected $table = "parts";

    protected $hidden = ['pivot'];

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
                $args[$parameterName] = $this->{$this->getKeyName()};
            } else {
                $args[$parameterName] = $this->{Str::snake($parameterName)};
            }
        }, (new \ReflectionClass(DSPart::class))->getConstructor()->getParameters());


        return new DSPart(...$args);
    }

    /**
     * @param self[]|Collection $parts
     * @return DSParts
     */
    public static function getDSParts(array|Collection $parts, string $gender): DSParts
    {
        $dsParts = new DSParts($gender);
        foreach ($parts as $part) {
            if (!($part instanceof Part)) {
                throw new \InvalidArgumentException('The variable $part must be of type: ' . Part::class, 500);
            }
            $dsParts[] = $part->getDSPart();
        }

        return $dsParts;
    }
}
