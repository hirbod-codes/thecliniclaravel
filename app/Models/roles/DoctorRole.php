<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSDoctor;

class DoctorRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "doctor_role";

    protected string $DS = DSDoctor::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getForeignKey(), (new User)->getKeyName(), __FUNCTION__);
    }
}
