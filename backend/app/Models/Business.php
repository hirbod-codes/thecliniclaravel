<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $name
 */
class Business extends Model
{
    use HasFactory;

    protected $table = "businesses";

    public function businessDefault(): HasOne
    {
        return $this->hasOne(BusinessDefault::class, $this->getForeignKey(), $this->getKeyName());
    }
}
