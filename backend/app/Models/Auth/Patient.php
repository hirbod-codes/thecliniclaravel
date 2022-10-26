<?php

namespace App\Models\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\Roles\PatientRole;
use App\Models\User;
use App\Models\Auth\Operator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property User $user belongsTo
 * @property PatientRole $role belongsTo
 * @property Operator $operator belongsTo
 * @property int $user_id FK -> User
 * @property int $patient_role_id FK -> PatientRole
 * @property int $operator_id FK -> Operator
 * @property int $user_guard_id FK -> user_guard
 */
class Patient extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "patients";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(
            Operator::class,
            (new Operator)->getForeignKey(),
            (new Operator)->getKeyName(),
            __FUNCTION__
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(PatientRole::class, (new PatientRole)->getForeignKey(), (new PatientRole)->getKeyName(), __FUNCTION__);
    }

    public function getRoleModelFullname(): string
    {
        return PatientRole::class;
    }
}
