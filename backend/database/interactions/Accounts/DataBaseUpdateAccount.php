<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Database\Interactions\Accounts\Interfaces\IDataBaseUpdateAccount;

class DataBaseUpdateAccount implements IDataBaseUpdateAccount
{
    public function massUpdateAccount(array $userAattributes, array $userRoleAattributes, User $user): User
    {
        $authenticatable = $user->authenticatableRole;

        try {
            DB::beginTransaction();

            if (!(isset($userAattributes) && $user->update($userAattributes)) || !(isset($userRoleAattributes) && $authenticatable->update($userRoleAattributes))) {
                throw new \RuntimeException('', 500);
            }

            DB::commit();

            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
