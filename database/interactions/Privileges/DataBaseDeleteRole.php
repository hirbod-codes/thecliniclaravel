<?php

namespace Database\Interactions\Privileges;

use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;

class DataBaseDeleteRole implements IDataBaseDeleteRole
{
    public function deleteRole(string $customRoleName): void
    {
        try {
            DB::beginTransaction();

            $role = Role::query()
                ->where('name', '=', $customRoleName)
                ->first();

            if ($role === null) {
                throw new ModelNotFoundException('', 404);
            }
            
            $role->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
