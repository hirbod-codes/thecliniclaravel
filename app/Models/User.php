<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\DSCustom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "users";

    protected string $DS = DSCustom::class;
}
