<?php

namespace Database\Interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\roles\CustomRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\User\DSUser;

class DataBaseCreateRole implements IDataBaseCreateRole
{
    use ResolveUserModel, TraitBaseUserRoleColumns;

    public function createRole(string $customRoleName, array $privilegeValue): void
    {
        try {
            DB::beginTransaction();

            $role = $this->findSimilarRole($customRoleName);

            if (is_null($role)) {
                return;
            }

            $this->createCustomeRoleDataStructure($customRoleName, $role);
            $this->createCustomeRoleModel($customRoleName, $role);
            $this->createCustomeRoleTable($customRoleName, $role);

            $role = new Role();
            $role->name = Str::snake($customRoleName);
            if (!$role->save()) {
                throw new \RuntimeException('Failed to create the role model.', 500);
            }

            foreach ($privilegeValue as $privilege => $value) {
                $privilegeValueModel = new PrivilegeValue;
                $privilegeValueModel->privilegeValue = $value;
                $privilegeValueModel->{(new Role)->getForeignKey()} = $role->getKey();
                $privilegeValueModel->{(new Privilege)->getForeignKey()} = Privilege::query()
                    ->where('name', '=', $privilege)
                    ->first()->getKey();
                if (!$privilegeValueModel->save()) {
                    throw new \RuntimeException('Failed to create the privilege-value model.', 500);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function createCustomeRoleModel(string $customRoleName, string|null $role): void
    {
        $customRoleName = Str::camel($customRoleName);

        $file = base_path() . '/app/Models/roles/UserDefined/' . $customRoleName . 'Role.php';

        $roleModelFullnameOriginal = $this->resolveRuleModelFullName($role);

        $roleModelFilePath = (new \ReflectionClass($roleModelFullnameOriginal))->getFileName();

        $content = file_get_contents($roleModelFilePath);
        $content = str_replace(class_basename($roleModelFullnameOriginal), $customRoleName . 'Role', $content);
        $content = str_replace('namespace App\\Models\\roles;', 'namespace App\\Models\\roles\\UserDefined;', $content);
        $content = str_replace($dsFullname = $this->resolveRuleDataStructureFullName($role), $customDSFullname = $this->resolveRuleDataStructureFullName($customRoleName), $content);
        $content = str_replace(class_basename($dsFullname), class_basename($customDSFullname), $content);

        file_put_contents($file, $content);
    }

    private function createCustomeRoleTable(string $customRoleName, string|null $role): void
    {
        $customRoleName = Str::camel($customRoleName);

        foreach (scandir(base_path() . '/database/migrations') as $file) {
            if (!is_file($file) || in_array($file, ['..', '.']) || !Str::contains($file, $role)) {
                continue;
            }

            $object = include($file);

            if (
                is_object($object) &&
                method_exists($object, 'up') &&
                ($parentClass = (new \ReflectionClass($object))->getParentClass()) !== false &&
                $parentClass->getName() === Migration::class
            ) {
                $object->up(Str::snake($customRoleName) . '_roles', Str::snake($customRoleName));
            } else {
                continue;
            }

            $this->recreateCustomRolesTableTriggers(Str::snake($customRoleName));

            break;
        }
    }

    private function recreateCustomRolesTableTriggers(string $customRoleName): void
    {
        $table = (new CustomRole)->getTable();
        DB::statement('DROP TRIGGER before_' . $table . '_insert');

        $fk = (new CustomRole)->getKeyName();
        $fkUserRole = (new AdminRole)->getUserRoleNameFKColumnName();

        $unacceptableRoles = [];
        foreach (Role::all() as $role) {
            if (Str::contains($role, DSUser::$roles)) {
                $unacceptableRoles[] = $role;
            }
        }
        $unacceptableRoles = array_merge(DSUser::$roles, $unacceptableRoles);

        $condition = '';
        for ($i = 0; $i < count($unacceptableRoles); $i++) {
            $condition .= 'NEW.' . $fkUserRole . ' = \'' . $unacceptableRoles[$i] . '\' ';
            if ($i !== count($unacceptableRoles) - 1) {
                $condition .= '|| ';
            }
        }
        DB::statement(
            'CREATE TRIGGER before_' . $table . '_insert BEFORE INSERT ON ' . $table . '
                            FOR EACH ROW
                            BEGIN
                                IF ' . $condition . 'THEN
                                signal sqlstate \'45000\'
                                SET MESSAGE_TEXT = "Mysql insert trigger";
                                END IF;

                                INSERT INTO users_guard (id) VALUES (NEW.' . $fk . ');
                            END;'
        );

        DB::statement('DROP TRIGGER before_' . $table . '_update');

        DB::statement(
            'CREATE TRIGGER before_' . $table . '_update BEFORE UPDATE ON ' . $table . '
                            FOR EACH ROW
                            BEGIN
                                IF ' . $condition . 'THEN
                                signal sqlstate \'45000\'
                                SET MESSAGE_TEXT = "Mysql update trigger";
                                END IF;
                            END;'
        );
    }

    private function createCustomeRoleDataStructure(string $customRoleName, string|null $role): void
    {
        $customRoleName = Str::camel($customRoleName);

        $roleDSFullnameOriginal = $this->resolveRuleDataStructureFullName($role);

        $roleDSFilePath = (new \ReflectionClass($roleDSFullnameOriginal))->getFileName();

        $file = base_path() . '/app/Models/roles/UserDefined/DS' . $customRoleName . '.php';

        $content = file_get_contents($roleDSFilePath);
        $content = str_replace(class_basename($roleDSFullnameOriginal), 'DS' . $customRoleName, $content);
        $content = str_replace('namespace TheClinicDataStructures\\DataStructures\\User;', 'namespace App\\Models\\roles\\UserDefined;', $content);
        $content = str_replace('\'' . $role . '\'', Str::snake($customRoleName), $content);
        $content = str_replace(
            '
    public static function getUserPrivileges(string $roleName = ""): array
    {
        return include self::PRIVILEGES_PATH . "/secretaryPrivileges.php";
    }',
            '
    public function getPrivilege(string $privilege): mixed
    {
        if (
            ($role = Role::query()
                ->where(\'name\', \'=\', $this->getRuleName())
                ->first()
            ) !== null &&
            ($privilege = Privilege::query()
                ->where(\'name\', \'=\', $privilege)
                ->first()
            ) !== null &&
            ($privilegeValue = PrivilegeValue::query()
                ->where($role->getForeignKey(), \'=\', $role->getKey())
                ->where($privilege->getForeignKey(), \'=\', $privilege->getKey())
                ->first()
            ) !== null
        ) {
            return $privilegeValue->privilegeValue;
        } else {
            throw new ModelNotFoundException(\'Failed to find the role model.\', 404);
        }
    }

    public function privilegeExists(string $privilege): bool
    {
        /**
         * @var Role $role
         * @var PrivilegeValue[] $privilegeValues
         */
        if (($privilege = Privilege::query()->where(\'name\', \'=\', $privilege)->first()) === null ||
            count($privilegeValues = ($role = Role::query()->where(\'name\', \'=\', $this->getRuleName())->first())->privilegeValues) === 0
        ) {
            throw new ModelNotFoundException(\'Failed to find privilege values.\', 500);
        }

        foreach ($privilegeValues as $privilegeValue) {
            if ($privilegeValue->{$privilege->getForeignKey()} === $privilege->getKey()) {
                return true;
            }
        }

        return false;
    }

    public static function getUserPrivileges(string $roleName = ""): array
    {
        $privileges = [];

        /** @var PrivilegeValue $privilegeValue */
        foreach (Role::query()->where(\'name\', \'=\', $roleName)->first()->privilegeValues as $privilegeValue) {
            $privileges[$privilegeValue->privilege->name] = $privilegeValue->value;
        }

        return $privileges;
    }

    public function setPrivilege(string $privilege, mixed $value, IPrivilege $ip): void
    {
        $ip->setPrivilege($this, $privilege, $value);
    }
        ',
            $content
        );

        file_put_contents($file, $content);
    }
}
