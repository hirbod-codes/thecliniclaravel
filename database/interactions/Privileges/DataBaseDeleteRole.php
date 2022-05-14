<?php

namespace Database\Interactions\Privileges;

use App\Models\Role;
use Database\Traits\ResolveUserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;

class DataBaseDeleteRole implements IDataBaseDeleteRole
{
    use ResolveUserModel;

    public function deleteRole(string $customRoleName): void
    {
        try {
            if (in_array($customRoleName, DSUser::$roles)) {
                throw new \RuntimeException('This rule is unremovable.', 403);
            }

            DB::beginTransaction();

            $role = $this->findSimilarRole($customRoleName);

            if (!is_null($role)) {
                $this->deleteCustomeRoleTable($customRoleName);
                $this->deleteCustomeRoleModel($customRoleName);
                $this->deleteCustomeRoleDataStructure($customRoleName);
            }

            $role = Role::query()
                ->where('name', '=', $customRoleName)
                ->firstOrFail();

            $role->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function deleteCustomeRoleDataStructure(string $customRoleName): void
    {
        $roleDSFullnameOriginal = $this->resolveRuleDataStructureFullName($customRoleName);

        $roleDSFilePath = (new \ReflectionClass($roleDSFullnameOriginal))->getFileName();

        unlink($roleDSFilePath);
    }

    private function deleteCustomeRoleModel(string $customRoleName): void
    {
        $roleModelFullnameOriginal = $this->resolveRuleModelFullName($customRoleName);

        $roleModelFilePath = (new \ReflectionClass($roleModelFullnameOriginal))->getFileName();

        unlink($roleModelFilePath);
    }

    private function deleteCustomeRoleTable(string $customRoleName): void
    {
        foreach (scandir(base_path() . '/database/migrations') as $file) {
            if (!is_file($file) || in_array($file, ['..', '.']) || !Str::contains($file, Str::snake($customRoleName) . '_roles')) {
                continue;
            }

            $object = include($file);

            if (
                is_object($object) &&
                method_exists($object, 'down') &&
                ($parentClass = (new \ReflectionClass($object))->getParentClass()) !== false &&
                $parentClass->getName() === Migration::class
            ) {
                $object->down(Str::snake($customRoleName) . '_roles');
            } else {
                continue;
            }

            break;
        }
    }
}
