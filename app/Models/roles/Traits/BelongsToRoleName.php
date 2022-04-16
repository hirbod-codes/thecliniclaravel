<?php

namespace App\Models\roles\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRoleName
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            (new Role)->getForeignKeyForName(),
            'name',
            __FUNCTION__
        );
    }
}
