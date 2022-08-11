<?php

namespace App\Models\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\Roles\DoctorRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Doctor extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "doctors";

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(DoctorRole::class, (new DoctorRole)->getForeignKey(), (new DoctorRole)->getKeyName(), __FUNCTION__);
    }

    public function getRoleModelFullname(): string
    {
        return DoctorRole::class;
    }
}
