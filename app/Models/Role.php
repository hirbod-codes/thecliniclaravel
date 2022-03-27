<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Role extends Model
{
    use HasFactory;

    protected $table = "roles";

    protected $guarded = ['*'];

    public function privilegeValue(): HasMany
    {
        return $this->hasMany(
            PrivilegeValue::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            (new Role)->getForeignKey(),
            'name'
        );
    }

    public function getForeignKey(): string
    {
        return strtolower(class_basename(static::class)) . 'name';
    }
}
