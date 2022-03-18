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
            (new Rule)->getForeignKey(),
            (new Rule)->getKeyName()
        );
    }

    public function guardRuleForeignKey(): void
    {
        $this->guarded[] = (new Rule)->getForeignKey();
    }

    private function addRuleForeignKey(): void
    {
        $this->foreignKeys[lcfirst(class_basename(Rule::class))] = (new Rule)->getForeignKey();
    }
}
