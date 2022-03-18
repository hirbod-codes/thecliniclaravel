<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\DSCustom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable;

    protected $table = "users";

    protected string $DS = DSCustom::class;

    public function getDataStructure(array $additionalArgs = []): DSCustom
    {
        return parent::getDataStructure(array_merge($additionalArgs, ['ruleName' => $this->rule()->first()->name]));
    }
}
