<?php

namespace App\Models\rules;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Secretary extends Model
{
    use HasFactory;

    protected $table = "secretaries";

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey(),
            (new Rule)->getKey()
        );
    }
}
