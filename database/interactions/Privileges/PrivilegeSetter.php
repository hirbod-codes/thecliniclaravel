<?php

namespace Database\Interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use TheClinicUseCases\Privileges\Interfaces\IPrivilegeSetter;

class PrivilegeSetter implements IPrivilegeSetter
{
    public function setPrivilege(string $roleName, array $privilegeValues): void
    {
        DB::beginTransaction();
        try {
            /** @var Role $role */
            $role = Role::query()->where('name', '=', $roleName)->firstOrFail();

            /** @var PrivilegeValue $privilegeValue */
            foreach ($role->privilegeValues as $privilegeValue) {
                $privilegeValue->delete();
            }

            unset($privilegeValue);

            foreach ($privilegeValues as $privilegeName => $privilegeValue) {
                $privilegeValueModel = new PrivilegeValue;
                $privilegeValueModel->{$role->getForeignKey()} = $role->getKey();
                $privilegeValueModel->{(new Privilege)->getForeignKey()} = Privilege::query()->where('name', '=', $privilegeName)->firstOrFail()->getKey();
                $privilegeValueModel->privilegeValue = $privilegeValue;
                $privilegeValueModel->saveOrFail();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
