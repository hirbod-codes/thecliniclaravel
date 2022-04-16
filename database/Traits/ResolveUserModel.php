<?php

namespace Database\Traits;

use App\Models\Auth\User as Authenticatable;
use App\Models\Model;
use App\Models\roles\CustomRole;
use App\Models\roles\DSCustom;
use App\Models\User;
use Database\Factories\roles\CustomRoleFactory;
use TheClinicDataStructures\DataStructures\User\DSUser;

trait ResolveUserModel
{
    public function resolveRuleName(DSUser|Authenticatable|User|string $modelFullname): string
    {
        if ($modelFullname instanceof DSUser) {
            return $modelFullname->getRuleName();
        } elseif ($modelFullname instanceof Authenticatable) {
            return $modelFullname->{$modelFullname->getUserRoleNameFKColumnName()};
        } elseif ($modelFullname instanceof User) {
            return $modelFullname->authenticatableRole->{$modelFullname->getUserRoleNameFKColumnName()};
        } elseif (is_string($modelFullname)) {
            if (!class_exists($modelFullname, false)) {
                throw new \InvalidArgumentException('', 500);
            }

            return $this->resolveRuleName(new $modelFullname);
        }
    }

    public function resolveRuleModelFullName(string $roleName): string
    {
        if (!in_array($roleName, DSUser::$roles)) {
            return CustomRole::class;
        }

        return 'App\\Models\\roles\\' . ucfirst($roleName) . 'Role';
    }

    public function resolveRuleDataStructureFullName(string $roleName): string
    {
        if (!in_array($roleName, DSUser::$roles)) {
            return DSCustom::class;
        }

        return 'TheClinicDataStructures\\DataStructures\\User\\DS' . ucfirst($roleName);
    }

    public function resolveRuleFactoryFullName(string $roleName): string
    {
        if (!in_array($roleName, DSUser::$roles)) {
            return CustomRoleFactory::class;
        } else {
            return 'Database\\Factories\\roles\\' . ucfirst($roleName) . 'RoleFactory';
        }
    }
}
