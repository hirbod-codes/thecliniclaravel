<?php

namespace App\Models\Privileges;

use App\Models\Model;
use App\Models\Role;
use App\Models\UserColumn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Role $relatedSubject belongsTo
 * @property Role $relatedObject belongsTo
 * @property UserColumn $relatedColumn belongsTo
 * @property int $subject FK -> Role
 * @property int $object FK -> Role
 * @property int $update_user_user_columns_user_column_id FK -> UserColumn
 */
class UpdateUser extends Model
{
    use HasFactory;

    protected $table = "update_user";

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

    public function relatedColumn(): BelongsTo
    {
        return $this->belongsTo(
            UserColumn::class,
            (new UserColumn)->getForeignKey(),
            (new UserColumn)->getKeyName()
        );
    }
}
