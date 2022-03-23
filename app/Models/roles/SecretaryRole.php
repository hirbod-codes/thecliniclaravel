<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSSecretary;

class SecretaryRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "secretary_rule";

    protected string $DS = DSSecretary::class;
}
