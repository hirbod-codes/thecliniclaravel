<?php

namespace App\Models;

use App\Models\rules\Traits\HasAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rule extends Model
{
    use HasFactory, HasAuthenticatable;

    protected $table = "rules";

    protected $guarded = ['*'];

    public function privilegeValue(): HasMany
    {
        return $this->hasMany(
            PrivilegeValue::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }
}
