<?php

namespace Database\Traits;

use App\Models\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\roles\CustomRole;
use App\Models\User;
use Database\Factories\roles\CustomRoleFactory;
use TheClinicDataStructures\DataStructures\User\DSUser;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\User\DSCustom;

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
            $roleName = $this->resolveRuleType($roleName);
        }

        if (class_exists($classFullname = 'App\\Models\\roles\\' . Str::studly($roleName) . 'Role')) {
            return $classFullname;
        } elseif (class_exists($classFullname = 'App\\Models\\roles\\UserDefined\\' . Str::studly($roleName) . 'Role')) {
            return $classFullname;
        }

        return CustomRole::class;
    }

    public function resolveRuleDataStructureFullName(string $roleName): string
    {
        if (!in_array($roleName, DSUser::$roles)) {
            $roleName = $this->resolveRuleType($roleName);
        }

        if (class_exists($classFullname = 'TheClinicDataStructures\\DataStructures\\User\\DS' . Str::studly($roleName))) {
            return $classFullname;
        } elseif (class_exists($classFullname = 'App\\Models\\roles\\UserDefined\\DS' . Str::studly($roleName))) {
            return $classFullname;
        }

        return DSCustom::class;
    }

    public function resolveRuleFactoryFullName(string $roleName): string
    {
        if (!in_array($roleName, DSUser::$roles)) {
            $roleName = $this->resolveRuleType($roleName);
        }

        if (!class_exists($classFullname = 'Database\\Factories\\roles\\' . Str::studly($roleName) . 'RoleFactory')) {
            return CustomRoleFactory::class;
        }

        return $classFullname;
    }

    /**
     * For example takes custom doctor role: doctor_123 and gives custom_doctor.
     *
     * @param string $roleName
     * @return string
     */
    public function resolveRuleType(string $roleName): string
    {
        if (in_array($roleName, DSUser::$roles)) {
            return $roleName;
        }

        $roleName = Role::query()->where('name', '=', Str::snake($roleName))->firstOrFail()->role;

        return $roleName;
    }
}
