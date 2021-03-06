<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSCustomSecretary;

class CustomSecretaryRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "custom_secretary_roles";

    protected string $DS = DSCustomSecretary::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }
}
