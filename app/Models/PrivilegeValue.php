<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeValue extends Model
{
    use HasFactory;

    protected $table = "privileges";

    protected $guarded = ['*'];

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(
            Privilege::class,
            strtolower(class_basename(Privilege::class)) . '_' . (new Privilege)->getKey(),
            (new Privilege)->getKey()
        );
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey(),
            (new Rule)->getKey()
        );
    }
}
