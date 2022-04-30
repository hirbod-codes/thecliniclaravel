<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSPatient;

class PatientRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "patient_roles";

    protected string $DS = DSPatient::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(
            OperatorRole::class,
            (new OperatorRole)->getForeignKey(),
            (new OperatorRole)->getKeyName(),
            __FUNCTION__
        );
    }
}
