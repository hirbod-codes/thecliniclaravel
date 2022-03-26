<?php

namespace Tests\Unit\Traits;

use App\Models\Auth\User as Authenticatable;
use App\Models\roles\AdminRole;
use App\Models\roles\DoctorRole;
use App\Models\roles\OperatorRole;
use App\Models\roles\PatientRole;
use App\Models\roles\SecretaryRole;
use App\Models\User;

trait GetAuthenticatables
{
    private function getAuthenticatable(string $roleName): Authenticatable
    {
        return $this->getAuthenticatables(true)[$roleName];
    }

    private function getAuthenticatables(bool $randomId = false): array
    {
        $adminRoleId = (new AdminRole)->getKeyName();
        $doctorRoleId = (new DoctorRole)->getKeyName();
        $secretaryRoleId = (new SecretaryRole)->getKeyName();
        $operatorRoleId = (new OperatorRole)->getKeyName();
        $patientRoleId = (new PatientRole())->getKeyName();

        return [
            'admin' => AdminRole::where(
                $adminRoleId,
                $randomId ? $this->faker->numberBetween(2, AdminRole::orderBy($adminRoleId, 'desc')->first()->{$adminRoleId}) : 1
            )->first(),
            'doctor' => DoctorRole::where(
                $doctorRoleId,
                $randomId ? $this->faker->numberBetween(2, DoctorRole::orderBy($doctorRoleId, 'desc')->first()->{$doctorRoleId}) : 1
            )->first(),
            'secretary' => SecretaryRole::where(
                $secretaryRoleId,
                $randomId ? $this->faker->numberBetween(2, SecretaryRole::orderBy($secretaryRoleId, 'desc')->first()->{$secretaryRoleId}) : 1
            )->first(),
            'operator' => OperatorRole::where(
                $operatorRoleId,
                $randomId ? $this->faker->numberBetween(2, OperatorRole::orderBy($operatorRoleId, 'desc')->first()->{$operatorRoleId}) : 1
            )->first(),
            'patient' => PatientRole::where(
                $patientRoleId,
                $randomId ? $this->faker->numberBetween(2, PatientRole::orderBy($patientRoleId, 'desc')->first()->{$patientRoleId}) : 1
            )->first(),
        ];
    }
}
