<?php

namespace Database\Interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;

class DataBaseCreateRole implements IDataBaseCreateRole
{
    public function createRole(string $customRoleName, array $privilegeValue): void
    {
        try {
            DB::beginTransaction();

            $role = new Role();
            $role->name = $customRoleName;
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
}
