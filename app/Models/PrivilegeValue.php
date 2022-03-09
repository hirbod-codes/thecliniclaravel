<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeValue extends Model
{
    use HasFactory;

    protected $table = "privilegeValues";

    protected $guarded = ['*'];

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(
            Privilege::class,
            strtolower(class_basename(Privilege::class)) . '_' . (new Privilege)->getKeyName(),
            (new Privilege)->getKeyName()
        );
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKeyName(),
            (new Rule)->getKeyName()
        );
    }
}
