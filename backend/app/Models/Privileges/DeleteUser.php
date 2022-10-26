<?php

namespace App\Models\Privileges;

use App\Models\Model;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Role $relatedSubject belongsTo
 * @property Role $relatedObject belongsTo
 * @property int $subject FK -> Role
 * @property int $object FK -> Role
 */
class DeleteUser extends Model
{
    use HasFactory;

    protected $table = "delete_user";

    public function relatedSubject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'subject',
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
