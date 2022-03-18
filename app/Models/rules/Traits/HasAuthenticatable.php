<?php

namespace App\Models\rules\Traits;

use App\Models\rules\AdminRule;
use App\Models\rules\DoctorRule;
use App\Models\rules\OperatorRule;
use App\Models\rules\PatientRule;
use App\Models\rules\SecretaryRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasAuthenticatable
{
    use AuthenticatableKeys;

    public function adminRule(): HasMany
    {
        return $this->hasMany(
            AdminRule::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }

    public function doctorRule(): HasMany
    {
        return $this->hasMany(
            DoctorRule::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }

    public function secretaryRule(): HasMany
    {
        return $this->hasMany(
            SecretaryRule::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }

    public function operatorRule(): HasMany
    {
        return $this->hasMany(
            OperatorRule::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }

    public function patientRule(): HasMany
    {
        return $this->hasMany(
            PatientRule::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }

    public function user(): HasMany
    {
        return $this->hasMany(
            User::class,
            $this->getAuthenticatableForeignKey(),
            $this->getAuthenticatableLocalKey()
        );
    }
}
