<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Privilege extends Model
{
    use HasFactory;

    protected $table = "privileges";

    public function privilegeValues(): HasMany
    {
        return $this->hasMany(
            PrivilegeValue::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }
}
