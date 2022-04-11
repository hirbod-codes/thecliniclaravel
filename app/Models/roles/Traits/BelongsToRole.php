<?php

namespace App\Models\roles\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRole
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            (new Role)->getForeignKey(),
            'name',
            __FUNCTION__
        );
    }

    public function guardRoleForeignKey(): void
    {
        $this->guarded[] = (new Role)->getForeignKey();
    }

    private function addRoleForeignKey(): void
    {
        $this->foreignKeys[lcfirst(class_basename(Role::class))] = (new Role)->getForeignKey();
    }
}
