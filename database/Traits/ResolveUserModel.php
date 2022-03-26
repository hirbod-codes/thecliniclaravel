<?php

namespace Database\Traits;

use App\Models\Model;
use App\Models\roles\DSCustom;
use App\Models\User;
use TheClinicDataStructures\DataStructures\User\DSUser;

trait ResolveUserModel
{
    public function resolveRuleName(DSUser|Model|string $modelFullname): string
    {
        if ($modelFullname instanceof DSUser) {
            return str_replace('ds', '', strtolower(class_basename($modelFullname)));
        } else {
            return str_replace('role', '', strtolower(class_basename($modelFullname)));
        }
    }

    public function resolveRuleModelFullName(string $ruleName): string
    {
        if ($ruleName === 'custom') {
            return User::class;
        }

        return 'App\\Models\\roles\\' . ucfirst($ruleName) . 'Role';
    }

    public function resolveRuleDataStructureFullName(string $ruleName): string
    {
        if ($ruleName === 'custom') {
            return DSCustom::class;
        }

        return 'TheClinicDataStructures\\DataStructures\\User\\DS' . ucfirst($ruleName);
    }

    public function resolveRuleFactoryFullName(string $ruleName): string
    {
        return 'Database\\Factories\\roles\\' . ucfirst($ruleName) . 'RoleFactory';
    }
}
