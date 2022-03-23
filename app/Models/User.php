<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\DSCustom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSUser;

class User extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "users";

    protected string $DS = DSCustom::class;

    public function getDataStructure(array $additionalArgs = []): DSUser
    {
        return parent::getDataStructure(array_merge($additionalArgs, ['ruleName' => $this->rule()->first()->name]));
    }
}
