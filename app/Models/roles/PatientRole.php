<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSPatient;

class PatientRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "Patient_rule";

    protected string $DS = DSPatient::class;
}
