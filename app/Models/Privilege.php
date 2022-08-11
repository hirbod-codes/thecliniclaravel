<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Privilege extends Model
{
    use HasFactory;

    protected $table = "privileges";

    public function privilegeName(): BelongsTo
    {
        return $this->belongsTo(
            PrivilegeName::class,
            (new PrivilegeName)->getForeignKey(),
            (new PrivilegeName)->getKeyName()
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            (new Role)->getForeignKey(),
            (new Role)->getKeyName()
        );
    }

    public function relatedObject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'object',
            (new Role)->getKeyName()
        );
    }
}
