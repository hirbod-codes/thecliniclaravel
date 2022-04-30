<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSOperator;

class OperatorRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "operator_roles";

    protected string $DS = DSOperator::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(PatientRole::class, $this->getForeignKey(), $this->getKeyName());
    }

    protected function collectOtherDSArgs(array &$args, string $parameterName): void
    {
        parent::collectOtherDSArgs($args, $parameterName);

        if ($parameterName === 'dsPatients') {
            /** @var PatientRole $patient */
            foreach ($this->patients as $patient) {
                $args[$parameterName] = $patient->getDataStructure();
            }
        } else {
            // Do nothing for optional arguments.
        }
    }
}
