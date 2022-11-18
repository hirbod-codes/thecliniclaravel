<?php

namespace App\Models\Package;

use App\Models\Part\Part;
use App\Models\Package\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Part $part belongsTo
 * @property Package $package belongsTo
 * @property int $parts_packages_packages_package_id FK -> Package
 * @property int $parts_packages_parts_part_id FK -> Part
 */
class PartPackage extends Model
{
    use HasFactory;

    protected $table = "parts_packages";

    public function part(): BelongsTo
    {
        return $this->belongsTo(
            Part::class,
            (new Part)->getForeignKey(),
            (new Part)->getKeyName(),
            __FUNCTION__
        );
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(
            Package::class,
            (new Package)->getForeignKey(),
            (new Package)->getKeyName(),
            __FUNCTION__
        );
    }
}
