<?php

namespace App\Models;

use App\Models\rules\Traits\HasAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rule extends Model
{
    use HasFactory, HasAuthenticatable;

    protected $table = "rules";

    protected $guarded = ['*'];

    public function privilegeValue(): HasOne
    {
        return $this->hasOne(
            PrivilegeValue::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKeyName(),
            $this->getKeyName()
        );
    }
}
