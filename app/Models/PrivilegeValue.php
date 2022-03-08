<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrivilegeValue extends Model
{
    use HasFactory;

    protected $fillable = ['value'];

    protected $table = "privileges_value";

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(
            Rule::class,
            (new Rule)->getTable() . '_' . (new static)->getTable(),
            strtolower(class_basename(static::class)) . '_' . (new static)->getKey(),
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey()
        )->withTimestamps();
    }

    // Mutators
    public function value(): Attribute
    {
        return Attribute::make(get: function ($value) {
            return $this->getValue($value);
        });
    }

    private function getValue(mixed $value): mixed
    {
        if (in_array($value, ['true', 'false'])) {
            return boolval($value);
        } elseif (is_numeric($value)) {
            return intval($value);
        } else {
            return strval($value);
        }
    }
}
