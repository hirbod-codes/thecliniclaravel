<?php

namespace Database\Traits;

use App\Models\rules\DSCustom;
use App\Models\User;

trait ResolveUserModel
{
    public function resolveRuleModelFullName(string $ruleName): string
    {
        if ($ruleName === 'custom') {
            return User::class;
        }

        return 'App\\Models\\rules\\' . ucfirst($ruleName) . 'Rule';
    }

    public function resolveRuleDataStructureFullName(string $ruleName): string
    {
        if ($ruleName === 'custom') {
            return DSCustom::class;
        }

        return 'TheClinicDataStructures\\DataStructures\\User\\DS' . ucfirst($ruleName);
    }
}

