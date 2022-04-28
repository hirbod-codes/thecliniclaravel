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
        $array = $this->getAuthenticatables(true);

        return $array[$roleName];
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
                )->firstOrFail() : AdminRole::orderBy($adminRoleId, 'desc')->firstOrFail(),
            'doctor' => $randomId ? DoctorRole::query()
                ->where(
                    $doctorRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('doctor'))
                )->firstOrFail() : DoctorRole::orderBy($doctorRoleId, 'desc')->firstOrFail(),
            'secretary' => $randomId ? SecretaryRole::query()
                ->where(
                    $secretaryRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('secretary'))
                )->firstOrFail() : SecretaryRole::orderBy($secretaryRoleId, 'desc')->firstOrFail(),
            'operator' => $randomId ? OperatorRole::query()
                ->where(
                    $operatorRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('operator'))
                )->firstOrFail() : OperatorRole::orderBy($operatorRoleId, 'desc')->firstOrFail(),
            'patient' => $randomId ? PatientRole::query()
                ->where(
                    $patientRoleId,
                    '=',
                    $this->faker->randomElement($this->getRandomId('patient'))
                )->firstOrFail() : PatientRole::orderBy($patientRoleId, 'desc')->firstOrFail(),
        ];
    }

    private function getRandomId(string $roleName): array
    {
        $modelFullname = $this->resolveRuleModelFullName($roleName);
        $modelPrimaryKey = (new $modelFullname)->getKeyName();

        $ids = array_map(function ($array) use ($modelPrimaryKey) {
            return $array[$modelPrimaryKey];
        }, $modelFullname::query()->orderBy($modelPrimaryKey, 'desc')->get([$modelPrimaryKey])->toArray());

        if (empty($ids)) {
            throw new \RuntimeException('Failed to find any id related to this role: ' . strval($roleName), 500);
        }

        array_shift($ids);

        return $ids;
    }
}
