<?php

namespace Database\Interactions\Privileges;

use App\Models\Role;
use Database\Traits\DataBaseHelpers;
use Database\Traits\ResolveUserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;

class DataBaseDeleteRole implements IDataBaseDeleteRole
{
    use ResolveUserModel, DataBaseHelpers;

    public function deleteRole(string $customRoleName): void
    {
        try {
            DB::beginTransaction();

            $role = Role::query()
                ->where('name', '=', $customRoleName)
                ->firstOrFail();

            $roleName = $role->role;

            $role->delete();

            DB::commit(DB::transactionLevel());
        } catch (\Throwable $th) {
            DB::rollBack(DB::transactionLevel());
            throw $th;
        }

        $this->updateRoleNameTriggers($roleName);
    }
}
