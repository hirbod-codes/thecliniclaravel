<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\TraitAuthResolver;
use Database\Interactions\Accounts\Interfaces\IDataBaseDeleteAccount;

class DataBaseDeleteAccount implements IDataBaseDeleteAccount
{
    use TraitAuthResolver;

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
