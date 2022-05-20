<?php

namespace Database\Interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\roles\AdminRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Database\Traits\DataBaseHelpers;
use Database\Traits\ResolveUserModel;
use Illuminate\Support\Facades\DB;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use Illuminate\Support\Str;

class DataBaseCreateRole implements IDataBaseCreateRole
{
    use ResolveUserModel, TraitBaseUserRoleColumns, DataBaseHelpers;

    public function createRole(string $customRoleName, array $privilegeValue, string $relatedRole): void
    {
        try {
            DB::beginTransaction();

            if (!Str::contains($relatedRole, 'custom')) {
                throw new \InvalidArgumentException('Invalid rule type!', 500);
            }

            $role = new Role();
            $role->name = Str::snake($customRoleName);
            $role->role = $relatedRole;
            $role->saveOrFail();

            foreach ($privilegeValue as $privilege => $value) {
                $privilegeValueModel = new PrivilegeValue;
                $privilegeValueModel->privilegeValue = $value;
                $privilegeValueModel->{(new Role)->getForeignKey()} = $role->getKey();
                $privilegeValueModel->{(new Privilege)->getForeignKey()} = Privilege::query()
                    ->where('name', '=', $privilege)
                    ->firstOrFail()
                    ->getKey();
                $privilegeValueModel->saveOrFail();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        if ($relatedRole === 'custom') {
            $this->updateRoleNameTriggers('custom');
        } else {
            $this->updateRoleNameTriggers($relatedRole);
        }
    }
}
