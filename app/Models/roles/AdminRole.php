<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSAdmin;

class AdminRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "admin_rule";

    protected string $DS = DSAdmin::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, (new User)->getForeignKey(), (new User)->getKeyName(), __FUNCTION__);
    }
}
