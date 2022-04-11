<?php

namespace App\Models\roles;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class CustomRole extends Model
{
    use HasFactory,
        Notifiable;

    protected $table = "custom_roles";

    protected string $DS = DSCustom::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }
}
