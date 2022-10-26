<?php

namespace App\Models;

use App\Models\Roles\AdminRole;
use App\Models\Roles\DoctorRole;
use App\Models\Roles\SecretaryRole;
use App\Models\Roles\OperatorRole;
use App\Models\Roles\PatientRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property AdminRole $adminRole hasOne
 * @property DoctorRole $doctorRole hasOne
 * @property OperatorRole $operatorRole hasOne
 * @property PatientRole $patientRole hasOne
 * @property SecretaryRole $secretaryRole hasOne
 */
class RoleGuard extends Model
{
    use HasFactory;

    protected $table = "roles_guard";

    protected $guarded = ['*'];

    public function adminRole(): HasOne
    {
        return $this->hasOne(
            AdminRole::class,
            $this->getForeignKey(),
            $this->getKeyName(),
        );
    }

    public function doctorRole(): HasOne
    {
        return $this->hasOne(
            DoctorRole::class,
            $this->getForeignKey(),
            $this->getKeyName(),
        );
    }

    public function secretaryRole(): HasOne
    {
        return $this->hasOne(
            SecretaryRole::class,
            $this->getForeignKey(),
            $this->getKeyName(),
        );
    }

    public function operatorRole(): HasOne
    {
        return $this->hasOne(
            OperatorRole::class,
            $this->getForeignKey(),
            $this->getKeyName(),
        );
    }

    public function patientRole(): HasOne
    {
        return $this->hasOne(
            PatientRole::class,
            $this->getForeignKey(),
            $this->getKeyName(),
        );
    }
}
