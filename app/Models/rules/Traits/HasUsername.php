<?php

namespace App\Models\rules\Traits;

use App\Models\Username;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUsername
{
    public function username(): BelongsTo
    {
        return $this->belongsTo(
            Username::class,
            strtolower(class_basename(Username::class)) . '_' . (new Username)->getKeyName(),
            (new Username)->getKey()
        );
    }
}
