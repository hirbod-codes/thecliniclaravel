<?php

namespace App\Models\rules\Traits;

use App\Models\Username;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUsername
{
    public function username(): BelongsTo
    {
        return $this->belongsTo(
            Username::class,
            lcfirst(class_basename(Username::class)),
            lcfirst(class_basename(Username::class)),
            __FUNCTION__
        );
    }

    private function addUsernameForeignKey(): void
    {
        $this->foreignKeys['username'] = 'username';
    }
}
