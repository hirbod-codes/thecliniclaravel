<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeValue extends Model
{
    use HasFactory;

    protected $table = "privilege_values";

    public function __construct(array $attributes = [])
    {
        $this->foreignKeys[lcfirst(class_basename(Privilege::class))] = (new Privilege)->getForeignKey();

        $this->foreignKeys[lcfirst(class_basename(Rule::class))] = (new Rule)->getForeignKey();

        parent::__construct($attributes);
    }

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(
            Privilege::class,
            (new Privilege)->getForeignKey(),
            (new Privilege)->getKeyName(),
            __FUNCTION__
        );
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            (new Rule)->getForeignKey(),
            (new Rule)->getKeyName(),
            __FUNCTION__
        );
    }
}
