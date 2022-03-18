<?php

namespace App\Models\rules;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSDoctor;

class DoctorRule extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable;

    protected $table = "doctor_rule";

    protected string $DS = DSDoctor::class;
}
