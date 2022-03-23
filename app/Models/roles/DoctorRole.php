<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSDoctor;

class DoctorRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "doctor_rule";

    protected string $DS = DSDoctor::class;
}
