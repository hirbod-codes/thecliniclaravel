<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\TraitRoleResolver;
use App\UseCases\Accounts\Interfaces\IDataBaseDeleteAccount;

class DataBaseDeleteAccount implements IDataBaseDeleteAccount
{
    use TraitRoleResolver;

    public function deleteAccount(User $user): void
    {
        try {
            DB::beginTransaction();

            DB::table((new User)->getTable())->delete($user->getKey());

            DB::commit();

            Storage::disk('local')->delete('images/avatars' . strval($user->getKey()));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
