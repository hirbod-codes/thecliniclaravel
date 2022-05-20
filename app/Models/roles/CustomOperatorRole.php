<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSCustomOperator;
use TheClinicDataStructures\DataStructures\User\DSPatients;

class CustomOperatorRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "custom_operator_roles";

    protected string $DS = DSCustomOperator::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(PatientRole::class, $this->getForeignKey(), $this->getKeyName());
    }

    public function customPatients(): HasMany
    {
        return $this->hasMany(CustomPatientRole::class, $this->getForeignKey(), $this->getKeyName());
    }

    protected function collectOtherDSArgs(array &$args, string $parameterName): void
    {
        parent::collectOtherDSArgs($args, $parameterName);

        if ($parameterName === 'dsPatients') {
            $patients = new DSPatients();
            /** @var PatientRole $patient */
            foreach ($this->patients as $patient) {
                $patients[] = $patient->getDataStructure();
            }
            /** @var CustomPatientRole $customPatient */
            foreach ($this->customPatients as $customPatient) {
                $patients[] = $customPatient->getDataStructure();
            }
            $args[$parameterName] = $patients;
        } else {
            // Do nothing for optional arguments.
        }
    }
}
