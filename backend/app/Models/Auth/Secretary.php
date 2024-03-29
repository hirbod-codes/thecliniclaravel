<?php

namespace App\Models\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\Roles\SecretaryRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property User $user belongsTo
 * @property SecretaryRole $role belongsTo
 * @property int $user_id FK -> User
 * @property int $secretary_role_id FK -> SecretaryRole
 * @property int $user_guard_id FK -> user_guard
 */
class Secretary extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "secretaries";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SecretaryRole::class, (new SecretaryRole)->getForeignKey(), (new SecretaryRole)->getKeyName(), __FUNCTION__);
    }

    public function getRoleModelFullname(): string
    {
        return SecretaryRole::class;
    }
}
