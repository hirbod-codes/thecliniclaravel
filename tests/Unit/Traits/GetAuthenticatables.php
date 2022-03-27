<?php

namespace Tests\Unit\Traits;

use App\Models\Auth\User as Authenticatable;
use App\Models\roles\AdminRole;
use App\Models\roles\DoctorRole;
use App\Models\roles\OperatorRole;
use App\Models\roles\PatientRole;
use App\Models\roles\SecretaryRole;
use App\Models\User;
use Database\Traits\ResolveUserModel;

trait GetAuthenticatables
{
    use ResolveUserModel;

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
            'admin' => $randomId ? AdminRole::query()
                ->where(
                    $adminRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('admin'))
                )->first() : AdminRole::orderBy($adminRoleId, 'desc')->first(),
            'doctor' => $randomId ? DoctorRole::query()
                ->where(
                    $doctorRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('doctor'))
                )->first() : DoctorRole::orderBy($doctorRoleId, 'desc')->first(),
            'secretary' => $randomId ? SecretaryRole::query()
                ->where(
                    $secretaryRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('secretary'))
                )->first() : SecretaryRole::orderBy($secretaryRoleId, 'desc')->first(),
            'operator' => $randomId ? OperatorRole::query()
                ->where(
                    $operatorRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('operator'))
                )->first() : OperatorRole::orderBy($operatorRoleId, 'desc')->first(),
            'patient' => $randomId ? PatientRole::query()
                ->where(
                    $patientRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('patient'))
                )->first() : PatientRole::orderBy($patientRoleId, 'desc')->first(),
        ];
    }

    private function getRandomId(string $roleName): array
    {
        $modelFullname = $this->resolveRuleModelFullName($roleName);
        $modelPrimaryKey = (new $modelFullname)->getKeyName();

        $ids = array_map(function ($array) use ($modelPrimaryKey) {
            return $array[$modelPrimaryKey];
        }, $modelFullname::query()->orderBy($modelPrimaryKey, 'desc')->get([$modelPrimaryKey])->toArray());

        array_shift($ids);

        return $ids;
    }
}
