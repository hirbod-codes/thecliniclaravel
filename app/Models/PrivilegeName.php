<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivilegeName extends Model
{
    use HasFactory;

    protected $table = "privilege_names";

    public function privileges(): HasMany
    {
        return $this->hasMany(
            Privilege::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }
}
