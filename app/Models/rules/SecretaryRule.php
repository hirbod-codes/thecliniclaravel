<?php

namespace App\Models\rules;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\Traits\BelongsToRule;
use App\Models\rules\Traits\HasDataStructure;
use App\Models\rules\Traits\BelongsToEmail;
use App\Models\rules\Traits\BelongsToPhonenumber;
use App\Models\rules\Traits\BelongsToUsername;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSSecretary;

class SecretaryRule extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable;

    protected $table = "secretary_rule";

    protected string $DS = DSSecretary::class;
}
