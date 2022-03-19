<?php

namespace Tests\Unit\Traits;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\AdminRule;
use App\Models\rules\DoctorRule;
use App\Models\rules\OperatorRule;
use App\Models\rules\PatientRule;
use App\Models\rules\SecretaryRule;
use App\Models\User;

trait GetAuthenticatables
{
    private function getAuthenticatable(string $ruleName): Authenticatable
    {
        return $this->getAuthenticatables(true)[$ruleName];
    }

    private function getAuthenticatables(bool $randomId = false): array
    {
        $adminRuleId = (new AdminRule)->getKeyName();
        $doctorRuleId = (new DoctorRule)->getKeyName();
        $secretaryRuleId = (new SecretaryRule)->getKeyName();
        $operatorRuleId = (new OperatorRule)->getKeyName();
        $patientRuleId = (new PatientRule())->getKeyName();
        $userId = (new User)->getKeyName();

        return [
            'admin' => AdminRule::where(
                $adminRuleId,
                $randomId ? $this->faker->numberBetween(2, AdminRule::orderBy($adminRuleId, 'desc')->first()->{$adminRuleId}) : 1
            )->first(),
            'doctor' => DoctorRule::where(
                $doctorRuleId,
                $randomId ? $this->faker->numberBetween(2, DoctorRule::orderBy($doctorRuleId, 'desc')->first()->{$doctorRuleId}) : 1
            )->first(),
            'secretary' => SecretaryRule::where(
                $secretaryRuleId,
                $randomId ? $this->faker->numberBetween(2, SecretaryRule::orderBy($secretaryRuleId, 'desc')->first()->{$secretaryRuleId}) : 1
            )->first(),
            'operator' => OperatorRule::where(
                $operatorRuleId,
                $randomId ? $this->faker->numberBetween(2, OperatorRule::orderBy($operatorRuleId, 'desc')->first()->{$operatorRuleId}) : 1
            )->first(),
            'patient' => PatientRule::where(
                $patientRuleId,
                $randomId ? $this->faker->numberBetween(2, PatientRule::orderBy($patientRuleId, 'desc')->first()->{$patientRuleId}) : 1
            )->first(),
            'custom' => User::where(
                $userId,
                $randomId ? $this->faker->numberBetween(2, User::orderBy($userId, 'desc')->first()->{$userId}) : 1
            )->first(),
        ];
    }
}
