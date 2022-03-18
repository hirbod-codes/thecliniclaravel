<?php

namespace App\Models\rules;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSPatient;

class PatientRule extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable;

    protected $table = "Patient_rule";

    protected string $DS = DSPatient::class;
}
