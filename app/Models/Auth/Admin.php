<?php

namespace App\Models\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\Roles\AdminRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property User $user belongsTo
 * @property AdminRole $role belongsTo
 * @property int $user_id FK -> User
 * @property int $admin_role_id FK -> AdminRole
 * @property int $user_guard_id FK -> user_guard
 */
class Admin extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "admins";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, (new AdminRole)->getForeignKey(), (new AdminRole)->getKeyName(), __FUNCTION__);
    }

    public function getRoleModelFullname(): string
    {
        return AdminRole::class;
    }
}
