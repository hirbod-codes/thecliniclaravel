<?php

namespace App\Models\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use App\Models\Roles\OperatorRole;
use App\Models\Auth\Patient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * @property User $user belongsTo
 * @property OperatorRole $role belongsTo
 * @property Collection<int, Patient> $patients hasMany
 * @property int $user_id FK -> User
 * @property int $operator_role_id FK -> OperatorRole
 * @property int $user_guard_id FK -> user_guard
 */
class Operator extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "operators";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, $this->getForeignKey(), $this->getKeyName());
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(OperatorRole::class, (new OperatorRole)->getForeignKey(), (new OperatorRole)->getKeyName(), __FUNCTION__);
    }

    public function getRoleModelFullname(): string
    {
        return OperatorRole::class;
    }
}
