<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\rules\Admin;
use App\Models\rules\Doctor;
use App\Models\rules\Operator;
use App\Models\rules\Patient;
use App\Models\rules\Secretary;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $table = "rules";

    public static array $rules = ['admin', 'doctor', 'secretary', 'operator', 'patient'];

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }

    public function privilegeValue(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivilegeValue::class,
            (new static)->getTable() . '_' . (new PrivilegeValue)->getTable(),
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            strtolower(class_basename(PrivilegeValue::class)) . '_' . (new PrivilegeValue)->getKey()
        )->withTimestamps();
    }

    public function admin(): HasOne
    {
        return $this->hasOne(
            Admin::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(
            Doctor::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }

    public function secretary(): HasOne
    {
        return $this->hasOne(
            Secretary::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }

    public function operator(): HasOne
    {
        return $this->hasOne(
            Operator::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }

    public function patient(): HasOne
    {
        return $this->hasOne(
            Patient::class,
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            $this->getKey()
        );
    }
}
