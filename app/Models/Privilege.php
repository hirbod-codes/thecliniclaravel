<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Privilege extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $table = "privileges";

    public function privilegeValue(): HasOne
    {
        return $this->hasOne(
            PrivilegeValue::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }
}
