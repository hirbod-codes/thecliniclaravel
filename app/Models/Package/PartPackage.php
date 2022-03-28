<?php

namespace App\Models\Package;

use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
