<?php

namespace App\Models\rules\Traits;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRule
{
    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey(),
            (new Rule)->getKey()
        );
    }

    public function guardRuleForeignKey(): void
    {
        $this->guarded[] = strtolower(class_basename(Rule::class)) . '_verified_at';
    }
}
