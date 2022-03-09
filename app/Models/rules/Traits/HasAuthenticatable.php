<?php

namespace App\Models\rules\Traits;

use App\Models\Rule;
use App\Models\rules\AdminRule;
use App\Models\rules\DoctorRule;
use App\Models\rules\OperatorRule;
use App\Models\rules\PatientRule;
use App\Models\rules\SecretaryRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasAuthenticatable
{
    private function getFK(): string
    {
        if (static::class === Rule::class) {
            return strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey();
        } else {
            return strtolower(class_basename(static::class));
        }
    }

    public function adminRule(): HasOne
    {
        return $this->hasOne(
            AdminRule::class,
            $this->getFK(),
            (new AdminRule)->getKey()
        );
    }

    public function doctorRule(): HasOne
    {
        return $this->hasOne(
            DoctorRule::class,
            $this->getFK(),
            (new DoctorRule)->getKey()
        );
    }

    public function secretaryRule(): HasOne
    {
        return $this->hasOne(
            SecretaryRule::class,
            $this->getFK(),
            (new SecretaryRule)->getKey()
        );
    }

    public function operatorRule(): HasOne
    {
        return $this->hasOne(
            OperatorRule::class,
            $this->getFK(),
            (new OperatorRule)->getKey()
        );
    }

    public function patientRule(): HasOne
    {
        return $this->hasOne(
            PatientRule::class,
            $this->getFK(),
            (new PatientRule)->getKey()
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            $this->getFK(),
            (new User)->getKey()
        );
    }
}
