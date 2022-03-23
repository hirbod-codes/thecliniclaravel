<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSOperator;

class OperatorRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "operator_rule";

    protected string $DS = DSOperator::class;
}
